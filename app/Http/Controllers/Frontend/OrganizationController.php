<?php

namespace App\Http\Controllers\Frontend;

use App\Exports\OrganizationExport;
use App\Http\Controllers\CommonFunction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Frontend\Concerns\SharesAccountNavigation;
use App\Models\Banners;
use App\Models\Categories;
use App\Models\OrganizationDetails;
use App\Models\OrganizationRatings;
use App\Models\Organizations;
use App\Models\PointOfContacts;
use App\Models\Publications;
use App\Models\SavedResources;
use App\Models\SavedSearchResources;
use App\Models\States;
use App\Models\SiteSettings;
use App\Models\SuggestOrganizations;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SpamReports;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    use SharesAccountNavigation;

    /** Publications shown per page on an organization's detail page. */
    private const PUBLICATION_PAGE = 6;

    public function resources(Request $request)
    {
        $states = States::orderBy('name', 'ASC')->get();
        $categories = Categories::orderBy('category_order', 'ASC')->get();
        $organizations = [];
        $resultedOrganizations = [];
        $queryParam = $request->all();
        $sort = strtolower((string) $request->get('sort', 'az'));
        $sort = in_array($sort, ['az', 'za'], true) ? $sort : 'az';
        $sortDirection = $sort === 'za' ? 'desc' : 'asc';
        $location_array = [];
        $organizations = Organizations::with('details')->where('organizations.status', 'active');
        if ($request->get('state') || $request->get('category') || $request->get('postal_code')) {
            $organizations = $organizations->leftJoin('organization_details', 'organization_details.organization_id', 'organizations.id');
            if ($request->get('state')) {
                $organizations = $organizations->where('organization_details.physical_state', $request->get('state'));
            }
            if ($request->get('category')) {
                $organizations = $organizations->whereJsonContains('organizations.category', $request->get('category'));
            }
            if ($request->get('organization_type')) {
                $organizations = $organizations->where('organizations.type', $request->get('organization_type'));
            }
            if ($request->get('target_population')) {
                $organizations = $organizations->where('organizations.target_population', $request->get('target_population'));
            }
            if ($request->get('organization_name')) {
                $organizations = $organizations->where('organizations.name', 'like', '%' . $request->get('organization_name') . '%');
            }
            if ($request->get('postal_code')) {
                $organizationsIds = OrganizationDetails::where('physical_postal_code', $request->get('postal_code'))->get('organization_id')->pluck('organization_id')->toArray();
                $organizations = $organizations->whereIn('organizations.id', $organizationsIds);
            }
            $resultedOrganizations = clone $organizations;
            $location_array = clone $organizations;
            $resultedOrganizations = $resultedOrganizations->get('organizations.id')->pluck('id')->toArray();
            $location_array = $location_array->select('organizations.name as name', 'organization_details.latitude as lat', 'organization_details.longitude as lng', 'organization_details.physical_state as state', 'physical_postal_code as postal_code', 'organizations.id as org_id', 'organizations.type as type')->orderBy('name', $sortDirection)->get()->toArray();
        } else {
            $location_array = Organizations::leftJoin('organization_details', 'organization_details.organization_id', 'organizations.id')
                ->where('organizations.status', 'active')
                ->select('organizations.name as name', 'organization_details.latitude as lat', 'organization_details.longitude as lng', 'organization_details.physical_state as state', 'physical_postal_code as postal_code', 'organizations.id as org_id', 'organizations.type as type')
                ->orderBy('name', $sortDirection)
                ->get()
                ->toArray();
        }
        $organizations = $organizations->orderBy('name', $sortDirection)->select('organizations.*');
        $organizations = $organizations->paginate(10);


        $authUser = auth()->user();
        if ($authUser && $authUser->role != 'admin') {
            $user = $authUser;
            $getStarDetails = function ($id) use ($user) {
                $ratingExists = false;
                $resourceExists = false;
                if ($user) {
                    $ratingExists = OrganizationRatings::where('organization_id', $id)->where('user_id', $user->id)->exists();
                    $resourceExists = SavedResources::where('organization_id', $id)->where('user_id', $user->id)->exists();
                }
                $data = [
                    'ratingExists' => $ratingExists,
                    'resourceExists' => $resourceExists,
                ];
                return $data;
            };
        } else {
            $getStarDetails = function ($id) {
                $data = [
                    'ratingExists' => false,
                    'resourceExists' => false,
                ];
                return $data;
            };
        }
        $getCategories = function ($categoryIds) {
            $categories = Categories::whereIn('id', json_decode($categoryIds))->get('name')->pluck('name')->toArray();
            return $categories;
        };
        $getPublications = function ($id) {
            $organization = Organizations::find($id);
            if (!$organization) {
                return [];
            }
            return $organization->publications()->orderBy('id', 'desc')->pluck('publications.title')->toArray();
        };
        $getRatings = function ($id) {
            $ratingDetails = CommonFunction::getRatingStars($id);
            return $ratingDetails;
        };
        $banners = Banners::where('page_slug', 'organization_details')->where('status', 'active')->orderBy('order', 'asc')->get();
        $resources = SiteSettings::where('settings_name', 'resource_block')->first()->settings_value ?? '';
        return view('frontend.organization.search-organization', compact('categories', 'states', 'organizations', 'getStarDetails', 'getCategories', 'getPublications', 'getRatings', 'queryParam', 'authUser', 'resultedOrganizations', 'location_array', 'banners', 'sort', 'resources'));
    }

    public function organizationDetails($id)
    {
        $organization = Organizations::find($id);
        if (!$organization) {
            return abort(404);
        }
        $location_array = Organizations::leftJoin('organization_details', 'organization_details.organization_id', 'organizations.id')->where('organizations.id', $id)->select('organizations.name as name', 'organization_details.latitude as lat', 'organization_details.longitude as lng', 'organization_details.physical_state as state', 'physical_postal_code as postal_code', 'organizations.id as org_id', 'organizations.type as type')->orderBy('organizations.id', 'desc')->get()->toArray();
        $categories = [];
        if (!empty($organization->category)) {
            $categories = Categories::whereIn('id', json_decode($organization->category))->get('name')->pluck('name')->toArray();
        }

        $organizationDetails = OrganizationDetails::where('organization_id', $id)->first();

        $publicationQuery = $organization->publications()->orderBy('id', 'desc');
        $publicationTotal = (clone $publicationQuery)->count();
        // The grid is a Vue island; it renders this first page straight from
        // props and asks /get-more-publication for the rest.
        $publications = $publicationQuery->take(self::PUBLICATION_PAGE)->get()
            ->map(fn (Publications $publication) => $this->publicationCard($publication))
            ->values();

        $user = auth()->user();
        $ratingExists = false;
        $resourceExists = false;
        if ($user && $user->role != 'admin') {
            $ratingExists = OrganizationRatings::where('organization_id', $id)->where('user_id', $user->id)->exists();
            $resourceExists = SavedResources::where('organization_id', $id)->where('user_id', $user->id)->exists();
        }
        $ratingDetails = CommonFunction::getRatingStars($organization->id);
        $banners = Banners::where('page_slug', 'organization_details')->where('status', 'active')->orderBy('order', 'asc')->get();
        return view('frontend.organization.organization-details', compact('banners', 'organization', 'organizationDetails', 'publications', 'publicationTotal', 'categories', 'user', 'ratingExists', 'resourceExists', 'ratingDetails', 'location_array'));
    }

    public function loadMorePublication($id, Request $request)
    {
        $offset = max(0, (int) $request->query('items', 0));
        $limit = self::PUBLICATION_PAGE;
        $organization = Organizations::find($id);
        if (!$organization) {
            return response()->json([
                'publications' => [],
                'hideBtn' => true,
            ]);
        }
        $publications = $organization->publications()->orderBy('id', 'desc');
        $publicationCount = (clone $publications)->count();
        $hideBtn = ($publicationCount - $offset) <= $limit;
        $publications = $publications->skip($offset)->take($limit)->get();

        return response()->json([
            // Same shape the page is rendered from, so the island can just
            // concatenate rather than rebuild markup from raw model columns.
            'publications' => $publications
                ->map(fn (Publications $publication) => $this->publicationCard($publication))
                ->values(),
            'hideBtn' => $hideBtn,
        ]);
    }

    /**
     * One publication card, with the URLs and share links resolved server side.
     */
    private function publicationCard(Publications $publication): array
    {
        $shareUrl = route('library.show', ['id' => $publication->id]);
        $shareLinks = \Share::page($shareUrl, $publication->title)
            ->facebook()
            ->twitter()
            ->linkedin()
            ->whatsapp()
            ->getRawLinks();

        return [
            'id' => $publication->id,
            'title' => $publication->title,
            'image' => $publication->image ? asset($publication->image) : asset('assets/img/image-here.png'),
            'file' => $publication->file ? asset($publication->file) : null,
            'downloadUrl' => url('download-resource/' . $publication->id),
            'share' => [
                'url' => $shareUrl,
                'title' => $publication->title,
                'facebook' => $shareLinks['facebook'] ?? '',
                'twitter' => $shareLinks['twitter'] ?? '',
                'linkedin' => $shareLinks['linkedin'] ?? '',
                'whatsapp' => $shareLinks['whatsapp'] ?? '',
            ],
        ];
    }

    public function reviewRating($id)
    {
        $organization = Organizations::findOrFail($id);

        return Inertia::render('Account/Review', [
            'submitUrl' => url('submit-review'),
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
            ],
            'states' => States::orderBy('name', 'ASC')->pluck('name')->values(),
            'legalSystems' => [
                'Formerly Incarcerated',
                'Currently Incarcerated',
                'Family or Friend',
                'Concerned Citizen',
                'Program Staff',
                'Legal Representative',
                'Educator',
                'Other',
            ],
        ]);
    }

    public function reviewSubmit(Request $request)
    {
        $rules = [
            'states' => 'required',
            'system_impacted' => 'required',
            'term_of_supervision' => 'required',
            'experience' => 'required',
            'initial_interaction' => 'required',
            'structured_involvement' => 'required',
            'mandated_by_the_courts' => 'required',
            'accurate_details' => 'required',
            'recommend' => 'required',
            'organization_id' => 'required',
            'rating' => 'required',
        ];
        if ($request->system_impacted == 'yes') {
            $rules['legal_system'] = 'required';
        }
        if ($request->structured_involvement == 'yes') {
            $rules['classroom_activities'] = 'required';
        }
        if ($request->accurate_details == 'no') {
            $rules['details'] = 'required';
        }
        $request->validate($rules);

        try {
            $details = [
                'states' => $request->states,
                'system_impacted' => $request->system_impacted  == 'yes' ? true : false,
                'legal_system' => $request->legal_system,
                'term_of_supervision' => $request->term_of_supervision == 'yes' ? true : false,
                'experience' => $request->experience,
                'initial_interaction' => $request->initial_interaction,
                'structured_involvement' => $request->structured_involvement == 'yes' ? true : false,
                'classroom_activities' => $request->classroom_activities == 'yes' ? true : false,
                'mandated_by_the_courts' => $request->mandated_by_the_courts == 'yes' ? true : false,
                'accurate_details' => $request->accurate_details == 'yes' ? true : false,
                'details' => $request->details,
                'recommend' => $request->recommend == 'yes' ? true : false
            ];

            $ratings = new OrganizationRatings();
            $ratings->user_id = auth()->user()->id;
            $ratings->organization_id = $request->organization_id;
            $ratings->rate = $request->rating;
            $ratings->description = json_encode($details);
            $ratings->save();

            // The organization detail page stays server rendered (it is the SEO
            // surface), so hand the browser a hard redirect rather than an
            // Inertia one.
            session()->flash('success', 'Review rating submitted successfully');

            return Inertia::location(url('/organization-details/' . $request->organization_id));
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function savedResource(Request $request, $id)
    {
        $user = auth()->user();
        if ($request->saved == 'exists') {
            SavedResources::where('user_id', $user->id)->where('organization_id', $id)->delete();
            return response()->json([
                'message' => 'Saved resource deleted successfully',
                'status' => 'success',
            ]);
        } else {
            $savedOrganization = new SavedResources();
            $savedOrganization->user_id = $user->id;
            $savedOrganization->organization_id = $id;
            $savedOrganization->save();
            if ($savedOrganization) {
                return response()->json([
                    'message' => 'Resource saved successfully',
                    'status' => 'success',
                ]);
            }
        }
        return response()->json([
            'errors' => 'An unexpected error occurred',
            'status' => 'error'
        ], 500);
    }

    public function downloadSavedResources()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->back()->with('error', 'No saved resource found.');
        }

        $savedResourcesCount = SavedResources::where('user_id', $user->id)->count();
        if ($savedResourcesCount === 0) {
            return redirect()->back()->with('error', 'No saved resources found.');
        }

        $pdfPath = $this->buildSavedResourcesPdf($user);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'No saved resources found.');
        }

        return response()->download($pdfPath);
    }

    private function buildSavedResourcesPdf($user): ?string
    {
        if (!$user) {
            return null;
        }

        $savedResources = SavedResources::where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->get('organization_id')
            ->pluck('organization_id')
            ->toArray();

        if (count($savedResources) === 0) {
            return null;
        }

        $fileName = $user->id . '-saved-resources.pdf';
        $directory = 'saved-resources';

        Storage::disk('public')->makeDirectory($directory);
        Storage::disk('public')->delete($directory . '/' . $fileName);

        $organizations = Organizations::whereIn('id', $savedResources)->get();
        $pages = $this->buildOrganizationPages($organizations);
        $pdfSaved = Pdf::loadView('pdf.saved-search-pdf', compact('pages'))
            ->setPaper('a4')
            ->set_option('defaultMediaType', 'all')
            ->set_option('isFontSubsettingEnabled', true)
            ->save(storage_path('app/public/' . $directory . '/' . $fileName));

        if (!$pdfSaved) {
            throw new \RuntimeException('Failed to save PDF');
        }

        return storage_path('app/public/' . $directory . '/' . $fileName);
    }

    public function savedResourceView()
    {
        $user = auth()->user();
        $savedResources = SavedResources::where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->pluck('organization_id')
            ->toArray();
        $organizations = Organizations::with('details')->whereIn('id', $savedResources)->get();

        return Inertia::render('Account/SavedResources', [
            'quickLinks' => $this->quickLinks(),
            'resources' => $organizations
                ->map(fn (Organizations $organization) => $this->resourceCard($organization, $user))
                ->values(),
        ]);
    }

    /**
     * One serialisation of an organization card, shared by every screen that
     * renders the search-box markup.
     *
     * @return array<string, mixed>
     */
    private function resourceCard(Organizations $organization, $user = null): array
    {
        $ratings = CommonFunction::getRatingStars($organization->id);

        return [
            'id' => $organization->id,
            'name' => $organization->name,
            'state' => $organization->details->physical_state ?? '',
            'type' => $organization->type == 'government'
                ? 'Government Organization'
                : 'Non-Government Organization',
            'categories' => Categories::whereIn('id', json_decode($organization->category ?? '', true) ?: [])
                ->pluck('name')
                ->toArray(),
            'description' => $organization->details->service_description ?? '',
            'publications' => $organization->publications()
                ->orderBy('id', 'desc')
                ->pluck('publications.title')
                ->toArray(),
            'ratingStars' => $ratings['starHtml'],
            'ratingCount' => $ratings['count'],
            'isMember' => $organization->is_member == '1',
            'detailsUrl' => url('/organization-details/' . $organization->id),
            'reviewUrl' => url('review-rating/' . $organization->id),
            'saveUrl' => url('saved-resource/' . $organization->id),
            'rated' => $user
                ? OrganizationRatings::where('organization_id', $organization->id)
                    ->where('user_id', $user->id)
                    ->exists()
                : false,
            'saved' => $user
                ? SavedResources::where('organization_id', $organization->id)
                    ->where('user_id', $user->id)
                    ->exists()
                : false,
        ];
    }

    public function saveSearch(Request $request)
    {
        try {
            $user = auth()->user();
            $ids = array_filter(explode(', ', $request->ids));
            if (count($ids) === 0) {
                return response()->json([
                    'message' => 'No organizations found',
                    'status' => 'error',
                ], 500);
            }

            DB::transaction(function () use ($user, $ids, $request) {
                $searchResources = new SavedSearchResources();
                $searchResources->user_id = $user->id;
                $searchResources->search_params = $request->search;
                $searchResources->save();

                $organizations = Organizations::leftJoin('organization_details', 'organization_details.organization_id', 'organizations.id')
                    ->where('organizations.status', 'active')
                    ->whereIn('organizations.id', $ids)
                    ->orderBy('organization_details.physical_state', 'ASC')
                    ->select('organizations.*')
                    ->get();

                $fileName =  $searchResources->id . '-' . $user->id . '-saved-search.pdf';
                $pages = $this->buildOrganizationPages($organizations);
                $pdfSaved = Pdf::loadView('pdf.saved-search-pdf', compact('pages'))
                    ->setPaper('a4')
                    ->set_option('defaultMediaType', 'all')
                    ->set_option('isFontSubsettingEnabled', true)
                    ->save(storage_path('app/public/search-resources/' . $fileName));

                if (!$pdfSaved) {
                    throw new \RuntimeException('Failed to save PDF');
                }
            });

            return response()->json([
                'message' => 'Search results saved successfully',
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => 'An unexpected error occurred: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
    public function pdfView(Request $request)
    {
        $organizations = Organizations::leftJoin('organization_details', 'organization_details.organization_id', 'organizations.id')->where('organizations.status', 'active')->orderBy('organization_details.physical_state', 'ASC')->select('organizations.*')->get();
        $pages = $this->buildOrganizationPages($organizations);

        if (isset($request->type) && $request->type == 'html') {
            return view('pdf.saved-search-pdf', compact('pages'));
        } else {
            return Pdf::loadView('pdf.saved-search-pdf', compact('pages'))
                ->setPaper('a4')
                ->set_option('defaultMediaType', 'all')
                ->set_option('isFontSubsettingEnabled', true)
                ->stream('document.pdf');
        }
    }

    /**
     * Build pages with columns sized by estimated content height to avoid overflow.
     */
    private function buildOrganizationPages(Collection $organizations): array
    {
        $firstPageMaxLines = 48; // tighter fit to account for cover header/footer
        $maxLinesPerColumn = 58; // subsequent pages
        $columnsPerPage = 3;
        $wrapWidth = 32; // characters per line before wrapping

        $measureLines = function (?string $text) use ($wrapWidth): int {
            $text = trim((string) $text);
            if ($text === '') {
                return 0;
            }
            $wrapped = explode("\n", wordwrap($text, $wrapWidth, "\n", true));
            return count($wrapped);
        };

        $estimateLinesForOrg = function ($organization) use ($measureLines): int {
            $details = $organization->organizationDetails();
            $lines = 0;

            $lines += $measureLines($organization->name);
            $lines += $measureLines($organization->type == 'government' ? 'Government Organization' : 'Non-Government Organization');

            $lines += $measureLines(optional($details)->physical_address_1);
            $cityStateZip = trim(
                ($details->physical_city ?? '') . ', ' .
                    ($details->physical_state ?? '') . ', ' .
                    ($details->physical_postal_code ?? ''),
                " ,"
            );
            $lines += $measureLines($cityStateZip);

            $lines += $measureLines('P: ' . ($organization->phone ?? ''));
            $lines += $measureLines('E: ' . ($organization->email ?? ''));
            $lines += $measureLines('W: ' . ($organization->website ?? ''));

            // Small buffer for margins between organizations
            return max(6, $lines + 1);
        };

        $pages = [];
        $lastState = null;
        $currentPage = [];
        $currentColumn = [];
        $currentLines = 0;
        $isFirstPage = true;
        $currentMaxLines = $firstPageMaxLines;

        foreach ($organizations as $organization) {
            $details = $organization->organizationDetails();
            $state = optional($details)->physical_state ?? 'Unknown';
            $isNewState = $state !== $lastState;

            $linesNeeded = $estimateLinesForOrg($organization);
            if ($isNewState) {
                $linesNeeded += $measureLines($state);
            }

            if (($currentLines + $linesNeeded) > $currentMaxLines && count($currentColumn) > 0) {
                $currentPage[] = $currentColumn;
                $currentColumn = [];
                $currentLines = 0;
            }

            if (count($currentPage) >= $columnsPerPage) {
                $pages[] = $currentPage;
                $currentPage = [];
                $isFirstPage = false;
                $currentMaxLines = $maxLinesPerColumn;
            }

            $organization->state_name = $state;
            $organization->show_state_heading = $isNewState;
            $lastState = $state;

            $currentColumn[] = $organization;
            $currentLines += $linesNeeded;
        }

        if (!empty($currentColumn)) {
            $currentPage[] = $currentColumn;
        }

        if (!empty($currentPage)) {
            $pages[] = $currentPage;
        }

        return $pages;
    }

    public function savedSearchView()
    {
        $user = auth()->user();
        $savedSearchs = SavedSearchResources::where('user_id', $user->id)->orderBy('id', 'ASC')->get();

        return Inertia::render('Account/SavedSearches', [
            'quickLinks' => $this->quickLinks(),
            'savedSearches' => $savedSearchs
                ->map(fn(SavedSearchResources $savedSearch) => $this->describeSavedSearch($savedSearch))
                ->values(),
        ]);
    }

    /**
     * Turn a stored search into the link + human label the saved-search list shows.
     *
     * @return array<string, mixed>
     */
    private function describeSavedSearch(SavedSearchResources $savedSearch): array
    {
        $search = json_decode($savedSearch->search_params, true) ?: [];

        $query = [];
        $labelParts = [];

        foreach ($search as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            $query[$key] = $value;

            if ($key === 'advance') {
                continue;
            }

            $readableKey = str_replace('_', ' ', ucfirst($key));
            $readableValue = $key === 'category'
                ? (Categories::find($value)->name ?? $value)
                : $value;

            $labelParts[] = $readableKey . '=' . $readableValue;
        }

        return [
            'id' => $savedSearch->id,
            'label' => implode(', ', $labelParts),
            'searchUrl' => url('search-resources') . ($query ? '?' . http_build_query($query) : ''),
            'downloadUrl' => url('/download-search/' . $savedSearch->id),
            'deleteUrl' => url('delete-search', $savedSearch->id),
        ];
    }
    public function downloadSearch($id)
    {
        $user = auth()->user();

        // Resolve through the user's own rows so the id cannot be used to shape
        // an arbitrary path into the storage directory.
        $search = SavedSearchResources::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$search) {
            return abort(404);
        }

        $fileName = $search->id . '-' . $user->id . '-saved-search.pdf';
        $filePath = storage_path('app/public/search-resources/' . $fileName);
        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return abort(404);
        }
    }
    public function deleteSearch(Request $request, $id)
    {
        try {
            // Scope to the signed-in user: the id comes straight off the URL, so
            // an unscoped lookup would let anyone delete anyone else's search.
            $search = SavedSearchResources::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$search) {
                return back()->with('error', 'Search Result could not found');
            }

            $fileName = $search->id . '-' . $search->user_id . '-saved-search.pdf';
            $filePath = storage_path('app/public/search-resources/' . $fileName);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $search->delete();

            return back()->with('success', 'Search Results deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }
    public function report_spam(Request $request)
    {
        $validator = validator($request->all(), [
            'org_id' => 'required|exists:organizations,id',
            'spam_reason' => 'required|max:350',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => implode("\n", $validator->errors()->all()),
                'status' => 'error'
            ], 422);
        }

        if (!auth()->check()) {
            return response()->json([
                'errors' => 'You need to be logged in to submit a report.',
                'status' => 'error'
            ], 401);
        }

        try {
            $organization = Organizations::find($request->org_id);
            if (!$organization) {
                return response()->json([
                    'errors' => 'Organization not found.',
                    'status' => 'error'
                ], 404);
            }

            $report = new SpamReports();
            $report->user_id = auth()->id();
            $report->organization_id = $request->org_id;
            $report->spam_reason = $request->spam_reason;
            $report->save();

            return response()->json([
                'message' => 'Report submitted successfully',
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => 'There was a problem, try again later.',
                'status' => 'error'
            ], 500);
        }
    }

    public function suggestNewOrganization()
    {
        return $this->renderSuggestForm('new');
    }

    public function suggestExistingOrganization()
    {
        return $this->renderSuggestForm('existing');
    }

    private function renderSuggestForm(string $type)
    {
        $states = States::orderBy('name', 'ASC')->get();

        return Inertia::render('Account/Suggest', [
            'type' => $type,
            'submitUrl' => route('submit-suggestions'),
            'fieldsUrl' => route('get-suggested-fields'),
            'checkTitlesUrl' => route('check-publication-titles'),
            'mapApiKey' => config('custom.map_api_key'),
            'placeholderImage' => asset('assets/img/placeholder.png'),
            // Address selects key by state name; publications key by state id.
            'states' => $states->pluck('name')->values(),
            'publicationStates' => $states->map(fn (States $state) => [
                'id' => (string) $state->id,
                'name' => $state->name,
            ])->values(),
            'categories' => Categories::orderBy('category_order', 'ASC')
                ->get()
                ->map(fn (Categories $category) => ['id' => $category->id, 'name' => $category->name])
                ->values(),
            'organizations' => Organizations::where('status', 'active')
                ->orderBy('name', 'ASC')
                ->get()
                ->map(fn (Organizations $organization) => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                ])
                ->values(),
            'values' => $this->suggestValues(),
        ]);
    }

    /**
     * The suggestion form's field set, blank or prefilled from a live
     * organization. One shape for both, so the Vue form can swap between them.
     *
     * @return array<string, mixed>
     */
    private function suggestValues(?Organizations $organization = null): array
    {
        $details = $organization
            ? OrganizationDetails::where('organization_id', $organization->id)->first()
            : null;
        $contact = $organization
            ? PointOfContacts::where('organization_id', $organization->id)->first()
            : null;
        $social = json_decode($details->social_links ?? '', true) ?: [];

        $publications = $organization
            ? $organization->publications()
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn (Publications $publication) => [
                    'publication_title' => $publication->title,
                    'publication_description' => $publication->description,
                    'publication_state' => (string) $publication->state,
                    // Carried through so the server can copy the existing asset
                    // when the user does not upload a replacement.
                    'cover_file_path' => $publication->file ?? '',
                    'cover_image_path' => $publication->image ?? '',
                    'cover_file_name' => $publication->file ? url($publication->file) : '',
                    'cover_image_name' => $publication->image ? url($publication->image) : '',
                    'publication_update_existing' => 1,
                ])
                ->values()
                ->all()
            : [];

        return [
            'existing_organization_value' => $organization->id ?? '',
            'organization_logo_prev' => $organization->logo ?? '',
            'logoUrl' => ($organization && $organization->logo) ? asset($organization->logo) : '',

            'organization_name' => $organization->name ?? '',
            'organization_type' => $organization->type ?? '',
            'email' => $organization->email ?? '',
            'phone' => preg_replace('/\D+/', '', (string) ($organization->phone ?? '')),
            'website' => $organization->website ?? '',

            'facebook' => $social['facebook'] ?? '',
            'linkedin' => $social['linkedin'] ?? '',
            'instagram' => $social['instagram'] ?? '',

            'service_description' => $details->service_description ?? '',
            'service_categories' => array_map(
                'intval',
                json_decode($organization->category ?? '', true) ?: []
            ),
            'service_area' => $organization->service_area_type ?? '',
            'service_state' => ($organization && $organization->service_area_type === 'state')
                ? (string) $organization->service_area
                : '',
            'target_population' => $organization->target_population ?? '',

            'point_of_contact_pronouns' => $contact->pronouns ?? 'He/Him',
            'point_of_contact_first_name' => $contact->first_name ?? '',
            'point_of_contact_last_name' => $contact->last_name ?? '',
            'point_of_contact_email' => $contact->email ?? '',
            'point_of_contact_phone' => preg_replace('/\D+/', '', (string) ($contact->phone ?? '')),
            'point_of_contact_notes' => $contact->notes ?? '',

            'address_1' => $details->physical_address_1 ?? '',
            'address_2' => $details->physical_address_2 ?? '',
            'city' => $details->physical_city ?? '',
            'state' => $details->physical_state ?? '',
            'postcode' => $details->physical_postal_code ?? '',
            'latitude' => $details->latitude ?? '',
            'longitude' => $details->longitude ?? '',

            'mailing_address_1' => $details->mailing_address_1 ?? '',
            'mailing_address_2' => $details->mailing_address_2 ?? '',
            'mailing_city' => $details->mailing_city ?? '',
            'mailing_state' => $details->mailing_state ?? '',
            'mailing_postcode' => $details->mailing_postal_code ?? '',

            'publications' => $publications,
        ];
    }

    /**
     * Field values for the suggestion form, as JSON.
     *
     * This used to render two Blade partials (new-suggested-fields /
     * existing-suggested-fields) that the page swapped in over AJAX; the Vue
     * form owns the markup now and only needs the data.
     */
    public function getFields(Request $request)
    {
        $organizationId = $request->query->get('organization');

        if (!$organizationId) {
            return response()->json(['values' => $this->suggestValues()]);
        }

        $organization = Organizations::find($organizationId);

        if (!$organization) {
            return response()->json([
                'errors' => 'No Organization details found',
                'status' => 'error',
            ], 404);
        }

        return response()->json(['values' => $this->suggestValues($organization)]);
    }

    public function checkPublicationTitles(Request $request)
    {
        $titles = $request->input('titles', []);
        if (!is_array($titles)) {
            return response()->json(['duplicates' => []]);
        }
        $titles = array_values(array_unique(array_filter(array_map('trim', $titles))));
        if (count($titles) === 0) {
            return response()->json(['duplicates' => []]);
        }

        $duplicates = Publications::whereIn('title', $titles)->get(['id', 'title']);
        return response()->json(['duplicates' => $duplicates]);
    }

    public function suggestOrganizationSubmit(Request $request)
    {
        $rules = [
            'organization_name' => 'required|string',
            'organization_type' => 'required|string',
            'phone' => 'required|regex:/^[0-9]{10,20}$/',
            'email' => 'required|email',
            'website' => 'required|url',
            'service_categories' => 'required',
            'service_area' => 'required',
            'target_population' => 'required',
            'organization_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',

            'point_of_contact_pronouns' => 'nullable|string',
            'point_of_contact_first_name' => 'nullable|string',
            'point_of_contact_last_name' => 'nullable|string',
            'point_of_contact_email' => 'nullable|email',
            'point_of_contact_phone' => 'nullable|regex:/^[0-9]{10,20}$/',
            'point_of_contact_notes' => 'nullable|max:250',

            'address_1' => 'required|string',
            'city' => 'required',
            'state' => 'required',
            'postcode' => 'required|regex:/^\d{5}(-\d{4})?$/',
            'mailing_postcode' => 'nullable|regex:/^\d{5}(-\d{4})?$/',
            'file_url' => 'nullable|url',
            'service_description' => 'required|max:350',
            'description' => 'nullable|max:250',
            'facebook' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?facebook\.com\/[a-zA-Z0-9(\.\?)?]+$/',
            'linkedin' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?linkedin\.com\/in\/[a-zA-Z0-9_-]+$/',
            'instagram' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?instagram\.com\/[a-zA-Z0-9_.]+$/',
            'twitter' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?twitter\.com\/[a-zA-Z0-9_]+$/',

            'publication_title.*' => 'nullable|string',
            'publication_description.*' => 'nullable|string',
            'publication_state.*' => 'nullable',
            'cover_file.*' => 'nullable|file|max:10240',
            'cover_image.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ];
        if (!empty($request->service_area) && $request->service_area == 'state') {
            $rules['service_state'] = ['required'];
        }
        $messages = [
            'postcode.regex' => 'The postcode must be in the format XXXXX or XXXXX-XXXX.',
            'mailing_postcode.regex' => 'The mailing postcode must be in the format XXXXX or XXXXX-XXXX.',
            'phone.regex' => 'The phone number must be between 10 and 20 digits.',
            'point_of_contact_phone.regex' => 'The point of contact phone number must be between 10 and 20 digits.',
            'facebook.regex' => 'The Facebook URL must be a valid profile link.',
            'linkedin.regex' => 'The LinkedIn URL must be a valid profile link.',
            'instagram.regex' => 'The Instagram URL must be a valid profile link.',
            'twitter.regex' => 'The Twitter URL must be a valid profile link.',
            'latitude.required' => 'Please select a valid address from the suggestions.',
            'longitude.required' => 'Please select a valid address from the suggestions.',
            'latitude.numeric' => 'Please select a valid address from the suggestions.',
            'longitude.numeric' => 'Please select a valid address from the suggestions.',
        ];
        $request->validate($rules, $messages);

        try {
            $publicationTitles = [];
            foreach (($request->publication_title ?? []) as $pubKey => $title) {
                $title = trim((string) $title);
                $description = trim((string) ($request->publication_description[$pubKey] ?? ''));
                if ($title === '' || $description === '') {
                    continue;
                }
                $publicationTitles[] = $title;
            }
            $duplicateTitles = [];
            if (count($publicationTitles) > 0) {
                $duplicateTitles = Publications::whereIn('title', $publicationTitles)->get('title')->pluck('title')->toArray();
            }

            if ($request->suggestion_type === 'new' && count($duplicateTitles) > 0) {
                $updateFlags = $request->publication_update_existing ?? [];
                $blockedTitles = [];
                foreach (($request->publication_title ?? []) as $pubKey => $title) {
                    $title = trim((string) $title);
                    $description = trim((string) ($request->publication_description[$pubKey] ?? ''));
                    if ($title === '' || $description === '' || !in_array($title, $duplicateTitles, true)) {
                        continue;
                    }
                    $allowUpdate = !empty($updateFlags[$pubKey]);
                    if (!$allowUpdate) {
                        $blockedTitles[] = $title;
                    }
                }
                if (count($blockedTitles) > 0) {
                    return back()->withErrors([
                        'publication_title' => 'Publication title already exists: '
                            . implode(', ', array_unique($blockedTitles)),
                    ]);
                }
            }

            $er = [];
            $env = app()->environment();
            if ($env != 'local') {
                if (!CommonFunction::smtpValidateEmail($request->email)) {
                    $er['email'] = 'Organization email ping validation failed';
                }
                if (!CommonFunction::validateWebsite($request->website)) {
                    $er['website'] = 'Organization website ping validation failed';
                }
            }

            if (count($er) > 0) {
                return back()->withErrors($er);
            }
            $service_area = $request->service_state ?? '';
            if (!empty($request->service_area) && $request->service_area == 'local') {
                $service_area = $request->state;
            } elseif (!empty($request->service_area) && $request->service_area == 'national') {
                $service_area = 'National';
            }

            $user = auth()->user();
            $suggestedOrganization = new SuggestOrganizations();
            $suggestedOrganization->status = 'pending';
            $suggestedOrganization->suggestion_type = $request->suggestion_type;
            if ($request->suggestion_type) {
                $suggestedOrganization->organization_id = $request->existing_organization_value;
            }
            $suggestedOrganization->name = $request->organization_name;
            $suggestedOrganization->user_id = $user->id;
            $suggestedOrganization->type = $request->organization_type;
            $suggestedOrganization->phone = $request->phone;
            $suggestedOrganization->email = $request->email;
            $suggestedOrganization->website = $request->website;
            $suggestedOrganization->category = json_encode($request->service_categories);
            $suggestedOrganization->target_population = $request->target_population;
            $suggestedOrganization->service_area_type = $request->service_area;
            $suggestedOrganization->service_area = $service_area;
            $suggestedOrganization->save();
            if ($request->hasFile('organization_logo')) {
                $file = CommonFunction::fileUploadStorage($request->file('organization_logo'), 'suggested', $suggestedOrganization->id . '-organization');
                if (!empty($file)) {
                    $suggestedOrganization->logo = $file;
                }
            } elseif ($suggestedOrganization->suggestion_type == "existing" && $request->organization_logo_prev != '') {
                $file = Commonfunction::moveFileWithRename($request->organization_logo_prev, 'suggested', $suggestedOrganization->id . '-organization');
                if (!empty($file)) {
                    $suggestedOrganization->logo = $file;
                }
            }
            $pointOfContact = [
                "pronouns" => $request->point_of_contact_pronouns,
                "first_name" => $request->point_of_contact_first_name,
                "last_name" => $request->point_of_contact_last_name,
                "name" => $request->point_of_contact_first_name . ' ' . $request->point_of_contact_last_name,
                "email" => $request->point_of_contact_email,
                "phone" => $request->point_of_contact_phone,
                "notes" => $request->point_of_contact_notes,
            ];
            $suggestedOrganization->point_of_contact = json_encode($pointOfContact);
            $organizationDetails = [
                "additional_resource" => $request->additional_resource,
                "title" => $request->title,
                "description" => $request->description,
                "file_url" => $request->file_url,
                "source" => $request->source,
                "physical_address_1" => $request->address_1,

                "latitude" => $request->latitude,
                "longitude" => $request->longitude,

                "physical_address_2" => $request->address_2,
                "physical_city" => $request->city,
                "physical_state" => $request->state,
                "physical_postal_code" => $request->postcode,
                "mailing_address_1" => $request->mailing_address_1,
                "mailing_address_2" => $request->mailing_address_2,
                "mailing_city" => $request->mailing_city,
                "mailing_state" => $request->mailing_state,
                "mailing_postal_code" => $request->mailing_postcode,
                "service_description" => $request->service_description,
                "social_links" => [
                    'facebook' => $request->facebook,
                    'linkedin' => $request->linkedin,
                    'instagram' => $request->instagram,
                ],
            ];
            $suggestedOrganization->organization_details = json_encode($organizationDetails);
            $publicationDetails = [];
            if (count($request->publication_title ?? []) > 0) {
                foreach ($request->publication_title as $pubKey => $publication_title) {
                    $publication_title = trim((string) $publication_title);
                    $publication_description = trim((string) ($request->publication_description[$pubKey] ?? ''));
                    if ($publication_title === '' || $publication_description === '') {
                        continue;
                    }
                    $updateExisting = !empty($request->publication_update_existing[$pubKey]);
                    $publication_file = '';
                    $publication_image = '';
                    if (isset($request->cover_file[$pubKey])) {
                        $file = CommonFunction::fileUploadStorage($request->cover_file[$pubKey], 'suggested/publictions', $pubKey . '-file-');
                        if (!empty($file)) {
                            $publication_file = $file;
                        }
                    } elseif ($suggestedOrganization->suggestion_type == "existing" && $request->cover_file_path[$pubKey] != '') {
                        $file = Commonfunction::moveFileWithRename($request->cover_file_path[$pubKey], 'suggested/publictions', $pubKey . '-file-');
                        if (!empty($file)) {
                            $publication_file = $file;
                        }
                    }
                    if (isset($request->cover_image[$pubKey])) {
                        $image = CommonFunction::fileUploadStorage($request->cover_image[$pubKey], 'suggested/publictions', $pubKey . '-image-');
                        if (!empty($image)) {
                            $publication_image = $image;
                        }
                    } elseif ($suggestedOrganization->suggestion_type == "existing" && $request->cover_image_path[$pubKey] != '') {
                        $image = Commonfunction::moveFileWithRename($request->cover_image_path[$pubKey], 'suggested/publictions', $pubKey . '-image-');
                        if (!empty($image)) {
                            $publication_image = $image;
                        }
                    }
                    $publicationDetails[] = [
                        "publication_title" => $publication_title,
                        "publication_description" => $publication_description,
                        "publication_state" => $request->publication_state[$pubKey] ?? '',
                        "cover_file" => $publication_file,
                        "cover_image" => $publication_image,
                        "update_existing" => $updateExisting ? 1 : 0,
                    ];
                }
            }
            $suggestedOrganization->publications = json_encode($publicationDetails);
            $suggestedOrganization->save();

            $categoryNames = Categories::whereIn('id', $request->service_categories ?? [])->pluck('name')->toArray();
            $categoryList = count($categoryNames) > 0 ? implode(', ', $categoryNames) : 'N/A';
            $pointOfContactName = trim($request->point_of_contact_first_name . ' ' . $request->point_of_contact_last_name);
            $address = $request->address_1;
            if (!empty($request->address_2)) {
                $address .= ', ' . $request->address_2;
            }
            $address .= ', ' . $request->city . ', ' . $request->state . ' ' . $request->postcode;
            $organizationDetailsHtml =
                '<div>' .
                '<p><strong>Suggestion Type:</strong> ' . ucfirst($suggestedOrganization->suggestion_type ?? 'new') . '</p>' .
                '<p><strong>Organization Name:</strong> ' . $request->organization_name . '</p>' .
                '<p><strong>Organization Type:</strong> ' . ucfirst($request->organization_type) . '</p>' .
                '<p><strong>Phone:</strong> ' . $request->phone . '</p>' .
                '<p><strong>Email:</strong> ' . $request->email . '</p>' .
                '<p><strong>Website:</strong> ' . $request->website . '</p>' .
                '<p><strong>Service Categories:</strong> ' . $categoryList . '</p>' .
                '<p><strong>Target Population:</strong> ' . $request->target_population . '</p>' .
                '<p><strong>Service Area:</strong> ' . ($request->service_area ?? '') . ' (' . $service_area . ')</p>' .
                '<p><strong>Point of Contact:</strong> ' . $pointOfContactName . ' (' . $request->point_of_contact_email . ', ' . $request->point_of_contact_phone . ')</p>' .
                '<p><strong>Address:</strong> ' . $address . '</p>' .
                '<p><strong>Description:</strong> ' . $request->service_description . '</p>' .
                '</div>';

            $adminEmail = SiteSettings::where('settings_name', 'admin_email')->first()->settings_value ?? '';
            if (!empty($adminEmail)) {
                $email_template = 6;
                $headerLogo = SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png';
                $logo = url($headerLogo);
                $url = url('/');
                $copyRight = SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';
                $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->name ?? $user->email ?? 'User');
                $check_array = array('#Name#', '#SiteURL#', '#SiteLogo#', '#OrganizationDetails#', '#FooterCopyright#');
                $replace_array = array($userName, $url, $logo, $organizationDetailsHtml, $copyRight);
                CommonFunction::sendMail($email_template, $adminEmail, $check_array, $replace_array);
            }

            // Derive the landing page from the suggestion type rather than
            // trusting the posted `redirect` field, which was an open redirect.
            $target = $request->suggestion_type === 'existing'
                ? url('suggest-existing-resources')
                : url('suggest-new-resources');

            return redirect($target)->with('success', 'Organization Details suggested successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }
}
