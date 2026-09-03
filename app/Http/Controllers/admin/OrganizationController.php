<?php

namespace App\Http\Controllers\admin;

use App\Exports\OrganizationExport;
use App\Http\Controllers\CommonFunction;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessImports;
use App\Models\Categories;
use App\Models\OrganizationDetails;
use App\Models\OrganizationFields;
use App\Models\Organizations;
use App\Models\PointOfContacts;
use App\Models\Publications;
use App\Models\EmailTemplates;
use App\Models\SiteSettings;
use App\Models\SpamReports;
use App\Models\States;
use App\Models\SuggestOrganizations;
use App\Models\User;
use App\Http\Controllers\admin\PublicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Jobs\ValidateOrganizationsJob;
use App\Models\SavedResources;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Organizations, paginated and filterable.
     *
     * This was a server-side DataTable whose endpoint built every cell as an
     * HTML string — checkboxes, images, status buttons with inline `onclick`
     * handlers and action links were all concatenated markup returned inside
     * JSON. The endpoint is gone; the table renders from data.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $state = trim((string) $request->query('state', ''));

        $query = Organizations::query()
            ->leftJoin('organization_details', 'organization_details.organization_id', 'organizations.id')
            ->select(
                'organizations.*',
                DB::raw('(SELECT count(*) FROM spam_reports WHERE spam_reports.organization_id = organizations.id) as spam_count'),
            )
            ->orderBy('organizations.name', 'ASC');

        if ($state !== '') {
            $query->where('organization_details.physical_state', $state);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('organizations.name', 'like', '%' . $search . '%')
                    ->orWhere('organizations.email', 'like', '%' . $search . '%')
                    ->orWhere('organizations.status', 'like', '%' . $search . '%');
            });
        }

        $organizations = $query->paginate(10)->withQueryString();
        $organizations->getCollection()->transform(fn (Organizations $organization) => [
            'id' => $organization->id,
            'name' => ucwords((string) $organization->name),
            'email' => (string) $organization->email,
            'status' => $organization->status,
            'statusLabel' => ucfirst((string) $organization->status),
            'logo' => $organization->logo ? asset($organization->logo) : asset('assets/img/placeholder.png'),
            'spamCount' => (int) ($organization->spam_count ?? 0),
            'spamUrl' => route('admin.spam-report', $organization->id),
            'showUrl' => route('admin.organization.show', $organization->id),
            'editUrl' => route('admin.organization.edit', $organization->id),
            'destroyUrl' => route('admin.organization.destroy', $organization->id),
            'statusUrl' => route('admin.organization.status', $organization->id),
        ]);

        return Inertia::render('Organizations/Index', [
            'organizations' => $organizations,
            'filters' => ['search' => $search, 'state' => $state],
            'states' => States::orderBy('name', 'ASC')->pluck('name')->values(),
            'createUrl' => route('admin.organization.create'),
            'exportUrl' => route('admin.organization.export'),
            'bulkDestroyUrl' => route('admin.organization.bulk-destroy'),
            'validateUrl' => route('admin.organization.manual-validate'),
        ]);
    }

    public function manualValidate(Request $request)
    {
        try {
            dispatch(new ValidateOrganizationsJob(true));
            return back()->with('success', 'Validation job queued. It will run in the background.');
        } catch (\Exception $e) {
            return back()->with('error', 'Validation failed to queue: ' . $e->getMessage());
        }
    }

    public function spam_report(?string $id = null)
    {
        $reports = SpamReports::orderBy('spam_reports.id', 'DESC')
            ->join('organizations', 'organizations.id', 'spam_reports.organization_id')
            ->join('users', 'users.id', 'spam_reports.user_id')
            ->select(
                'spam_reports.*',
                'organizations.id as org_id',
                'organizations.name as org_name',
                'organizations.status as org_status',
                'users.name as user_name',
                'users.email as user_email',
                'users.id as user_id',
            );

        // The id used to be read off the last URL segment rather than the route
        // parameter, so /admin/spam-report filtered on the literal string
        // "spam-report" and only worked by accident.
        $organization = $id ? Organizations::find($id) : null;

        if ($organization) {
            $reports->where('organizations.id', $organization->id);
        }

        return Inertia::render('Organizations/SpamReport', [
            'organization' => $organization ? [
                'id' => $organization->id,
                'name' => $organization->name,
            ] : null,
            'reports' => $reports->get()->map(fn ($report) => [
                'id' => $report->id,
                'reason' => $report->spam_reason ?? '',
                'user' => [
                    'name' => ucwords((string) $report->user_name),
                    'email' => $report->user_email ?? '',
                    'editUrl' => route('admin.user.edit', $report->user_id),
                ],
                'organization' => [
                    'name' => ucwords((string) $report->org_name),
                    'status' => ucfirst((string) $report->org_status),
                    'editUrl' => route('admin.organization.edit', $report->org_id),
                ],
            ])->values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Organizations/Create', array_merge($this->formOptions(), [
            'submitUrl' => route('admin.organization.store'),
            'values' => $this->formValues(),
        ]));
    }

    /**
     * Reference data every organization form needs, shared by create and edit.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $states = States::orderBy('name', 'ASC')->get();

        return [
            'indexUrl' => route('admin.organization.index'),
            // Address selects store the state *name*; publications key by id.
            'states' => $states->pluck('name')->values(),
            'publicationStates' => $states->map(fn ($state) => [
                'id' => $state->id,
                'name' => $state->name,
            ])->values(),
            'categories' => Categories::orderBy('category_order', 'ASC')
                ->get()
                ->map(fn ($category) => ['id' => $category->id, 'name' => $category->name])
                ->values(),
            'publications' => Publications::where('status', 'active')
                ->get()
                ->map(fn ($publication) => ['id' => $publication->id, 'title' => $publication->title])
                ->values(),
            'placeholderImage' => asset('assets/img/placeholder.png'),
            'mapApiKey' => config('custom.map_api_key'),
            'publicationStoreUrl' => route('admin.publication.store'),
            'ckeditorUrl' => asset('plugins/ckeditor/ckeditor.js'),
        ];
    }

    /**
     * Flatten an organization into the exact request-field names the form posts,
     * so create and edit share one payload shape.
     *
     * @return array<string, mixed>
     */
    private function formValues(
        ?Organizations $organization = null,
        ?OrganizationDetails $details = null,
        ?PointOfContacts $contact = null,
        array $assignedPublicationIds = []
    ): array {
        $socialLinks = json_decode($details->social_links ?? '', true) ?: [];

        return [
            'organization_name' => $organization->name ?? '',
            'organization_type' => $organization->type ?? '',
            'email' => $organization->email ?? '',
            'phone' => preg_replace('/\D+/', '', (string) ($organization->phone ?? '')),
            'website' => $organization->website ?? '',

            'facebook' => $socialLinks['facebook'] ?? '',
            'linkedin' => $socialLinks['linkedin'] ?? '',
            'instagram' => $socialLinks['instagram'] ?? '',

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

            'additional_resource' => $details->additional_resource ?? '',
            'title' => $details->title ?? '',
            'description' => $details->description ?? '',

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

            'source' => $details->source ?? '',
            'is_member' => (bool) ($organization->is_member ?? false),
            'assign_publication' => array_map('intval', $assignedPublicationIds),
        ];
    }

    /**
     * Validation rules shared by store() and update().
     *
     * @return array<string, mixed>
     */
    private function validationRules(Request $request): array
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
            'file_url' => 'nullable|file|max:5120',
            'service_description' => 'required|max:350',
            'description' => 'nullable|max:250',
            'facebook' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?facebook\.com\/[a-zA-Z0-9(\.\?)?]+$/',
            'linkedin' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?linkedin\.com\/in\/[a-zA-Z0-9_-]+$/',
            'instagram' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?instagram\.com\/[a-zA-Z0-9_.]+$/',
            'twitter' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?twitter\.com\/[a-zA-Z0-9_]+$/',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ];

        if (!empty($request->service_area) && $request->service_area == 'state') {
            $rules['service_state'] = ['required'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
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
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate($this->validationRules($request), $this->validationMessages());

        try {
            $env = app()->environment();
            if ($env != 'local') {
                $er = [];
                if (!CommonFunction::smtpValidateEmail($request->email)) {
                    $er['email'] = 'Organization email ping validation failed';
                }
                if (!CommonFunction::validateWebsite($request->website)) {
                    $er['website'] = 'Organization website ping validation failed';
                }

                if (count($er) > 0) {
                    return back()->withErrors($er);
                }
            }

            $service_area = $request->service_state ?? '';
            if (!empty($request->service_area) && $request->service_area == 'local') {
                $service_area = $request->state;
            } elseif (!empty($request->service_area) && $request->service_area == 'national') {
                $service_area = 'National';
            }
            $organization = new Organizations();
            $organization->name = str_replace('`', '\'', $request->organization_name);
            $organization->type = $request->organization_type;
            $organization->phone = $request->phone;
            $organization->email = $request->email;
            $organization->website = $request->website;
            $organization->category = json_encode($request->service_categories);
            $organization->target_population = $request->target_population;
            $organization->service_area_type = $request->service_area;
            $organization->service_area = $service_area;
            $organization->save();
            if ($request->hasFile('organization_logo')) {
                $file = CommonFunction::fileUploadStorage($request->file('organization_logo'), 'organization', $organization->id . '-organization');
                if (!empty($file)) {
                    $organization->logo = $file;
                }
            }
            $organization->save();

            $organization->publications()->sync($request->assign_publication ?? []);
            $pointofcontacts = new PointOfContacts();
            $pointofcontacts->organization_id = $organization->id;
            $pointofcontacts->pronouns = $request->point_of_contact_pronouns;
            $pointofcontacts->first_name = $request->point_of_contact_first_name;
            $pointofcontacts->last_name = $request->point_of_contact_last_name;
            $pointofcontacts->name = $request->point_of_contact_first_name . ' ' . $request->point_of_contact_last_name;
            $pointofcontacts->email = $request->point_of_contact_email;
            $pointofcontacts->phone = $request->point_of_contact_phone;
            $pointofcontacts->notes = $request->point_of_contact_notes;
            $pointofcontacts->save();

            $organization_details = new OrganizationDetails();
            $organization_details->organization_id = $organization->id;
            $organization_details->additional_resource = $request->additional_resource;
            $organization_details->title = $request->title;
            $organization_details->description = $request->description;
            if ($request->hasFile('file_url')) {
                $file = CommonFunction::fileUploadStorage($request->file('file_url'), 'organization', $organization->id . '-file');
                if (!empty($file)) {
                    $organization_details->file_url = $file;
                }
            }
            $organization_details->source = $request->source;
            $organization_details->physical_address_1 = $request->address_1;
            $organization_details->physical_address_2 = $request->address_2;
            $organization_details->physical_city = $request->city;
            $organization_details->physical_state = $request->state;
            $organization_details->physical_postal_code = $request->postcode;

            $organization_details->latitude = $request->latitude;
            $organization_details->longitude = $request->longitude;

            $organization_details->mailing_address_1 = $request->mailing_address_1;
            $organization_details->mailing_address_2 = $request->mailing_address_2;
            $organization_details->mailing_city = $request->mailing_city;
            $organization_details->mailing_state = $request->mailing_state;
            $organization_details->mailing_postal_code = $request->mailing_postcode;
            $organization_details->service_description = $request->service_description;
            $social_links = [
                'facebook' => $request->facebook,
                'linkedin' => $request->linkedin,
                'instagram' => $request->instagram,
            ];
            $organization_details->social_links = json_encode($social_links);
            $organization_details->save();
            $organization->save();

            // Send confirmation email to the point of contact
            $template = EmailTemplates::where('name', 'organization_added_point_of_contact')->first();
            if ($template) {
                $emailTitle = 'Organization Added: ' . $organization->name;
                $headerLogo = SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png';
                $logo = url($headerLogo);
                $url = url('/');
                $copyRight = SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';
                $actionMessage = 'Your organization has been added to the Community Resource Directory directory.';
                $check_array = ['#EmailTitle#', '#Name#', '#OrganizationName#', '#ActionMessage#', '#SiteURL#', '#SiteLogo#', '#FooterCopyright#'];
                $replace_array = [
                    $emailTitle,
                    $pointofcontacts->name,
                    $organization->name,
                    $actionMessage,
                    $url,
                    $logo,
                    $copyRight,
                ];
                CommonFunction::sendMail($template->id, $pointofcontacts->email, $check_array, $replace_array);
            }

            // The organization index is still a Blade screen, so hand the browser
            // a hard redirect instead of an Inertia one.
            session()->flash('success', 'Organization Details added successfully');

            return Inertia::location(route('admin.organization.index'));
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * The read-only view of an organization.
     *
     * It was a fourth copy of the organization form with every input disabled;
     * it now renders the same OrganizationForm.vue with `readonly` set.
     */
    public function show(string $id)
    {
        $organization = Organizations::with('publications')->findOrFail($id);
        $organizationDetails = OrganizationDetails::where('organization_id', $id)->first();
        $pointofcontacts = PointOfContacts::where('organization_id', $id)->first();

        return Inertia::render('Organizations/Show', array_merge($this->formOptions(), [
            'name' => $organization->name,
            'editUrl' => route('admin.organization.edit', $organization->id),
            'submitUrl' => route('admin.organization.update', $organization->id),
            'values' => $this->formValues(
                $organization,
                $organizationDetails,
                $pointofcontacts,
                $organization->publications->pluck('id')->toArray(),
            ),
            'logoUrl' => $organization->logo ? asset($organization->logo) : '',
            'currentFileUrl' => $organizationDetails?->file_url ? url($organizationDetails->file_url) : '',
        ]));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $organization = Organizations::with('publications')->findOrFail($id);
        $organizationDetails = OrganizationDetails::where('organization_id', $id)->first();
        $pointofcontacts = PointOfContacts::where('organization_id', $id)->first();
        $assignedPublicationIds = $organization->publications->pluck('id')->toArray();

        return Inertia::render('Organizations/Edit', array_merge($this->formOptions(), [
            'submitUrl' => route('admin.organization.update', $organization->id),
            'values' => $this->formValues(
                $organization,
                $organizationDetails,
                $pointofcontacts,
                $assignedPublicationIds
            ),
            'logoUrl' => $organization->logo ? asset($organization->logo) : '',
            'currentFileUrl' => $organizationDetails?->file_url ? url($organizationDetails->file_url) : '',
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate($this->validationRules($request), $this->validationMessages());

        try {
            $organization = Organizations::findOrFail($id);
            $env = app()->environment();
            if ($env != 'local') {
                $er = [];
                $emailChanged = trim((string) $request->email) !== trim((string) $organization->email);
                if ($emailChanged && !CommonFunction::smtpValidateEmail($request->email)) {
                    $er['email'] = 'Organization email ping validation failed';
                }
                if (!CommonFunction::validateWebsite($request->website)) {
                    $er['website'] = 'Organization website ping validation failed';
                }

                if (count($er) > 0) {
                    return back()->withErrors($er);
                }
            }

            $service_area = $request->service_state ?? '';
            if (!empty($request->service_area) && $request->service_area == 'local') {
                $service_area = $request->state;
            } elseif (!empty($request->service_area) && $request->service_area == 'national') {
                $service_area = 'National';
            }
            $organization->name = str_replace('`', '\'', $request->organization_name);
            $organization->type = $request->organization_type;
            $organization->phone = $request->phone;
            $organization->email = $request->email;
            $organization->website = $request->website;
            $organization->category = json_encode($request->service_categories);
            $organization->target_population = $request->target_population;
            $organization->service_area_type = $request->service_area;
            $organization->service_area = $service_area;
            $organization->is_member = $request->is_member;
            $organization->save();
            if ($request->hasFile('organization_logo')) {
                $file = CommonFunction::fileUploadStorage($request->file('organization_logo'), 'organization', $organization->id . '-organization');
                if (!empty($file)) {
                    $organization->logo = $file;
                }
            }
            $organization->save();

            $organization->publications()->sync($request->assign_publication ?? []);
            $pointofcontacts = PointOfContacts::where('organization_id', $id)->first();
            if ($pointofcontacts) {
                $pointofcontacts->organization_id = $organization->id;
                $pointofcontacts->pronouns = $request->point_of_contact_pronouns;
                $pointofcontacts->first_name = $request->point_of_contact_first_name;
                $pointofcontacts->last_name = $request->point_of_contact_last_name;
                $pointofcontacts->name = $request->point_of_contact_first_name . ' ' . $request->point_of_contact_last_name;
                $pointofcontacts->email = $request->point_of_contact_email;
                $pointofcontacts->phone = $request->point_of_contact_phone;
                $pointofcontacts->notes = $request->point_of_contact_notes;
                $pointofcontacts->save();
            }

            $organization_details = OrganizationDetails::where('organization_id', $id)->first();
            $organization_details->organization_id = $organization->id;
            $organization_details->additional_resource = $request->additional_resource;
            $organization_details->title = $request->title;
            $organization_details->description = $request->description;
            if ($request->hasFile('file_url')) {
                $file = CommonFunction::fileUploadStorage($request->file('file_url'), 'organization', $organization->id . '-file');
                if (!empty($file)) {
                    CommonFunction::fileDeleteStorage($organization_details->file_url);
                    $organization_details->file_url = $file;
                }
            }
            $organization_details->source = $request->source;
            $organization_details->physical_address_1 = $request->address_1;
            $organization_details->physical_address_2 = $request->address_2;
            $organization_details->physical_city = $request->city;
            $organization_details->physical_state = $request->state;
            $organization_details->physical_postal_code = $request->postcode;

            $organization_details->latitude = $request->latitude;
            $organization_details->longitude = $request->longitude;

            $organization_details->mailing_address_1 = $request->mailing_address_1;
            $organization_details->mailing_address_2 = $request->mailing_address_2;
            $organization_details->mailing_city = $request->mailing_city;
            $organization_details->mailing_state = $request->mailing_state;
            $organization_details->mailing_postal_code = $request->mailing_postcode;
            $organization_details->service_description = $request->service_description;
            $social_links = [
                'facebook' => $request->facebook,
                'linkedin' => $request->linkedin,
                'instagram' => $request->instagram,
            ];
            $organization_details->social_links = json_encode($social_links);
            $organization_details->save();
            $organization->save();

            // Send confirmation email to the point of contact on update
            if ($pointofcontacts) {
                $template = EmailTemplates::where('name', 'organization_added_point_of_contact')->first();
                if ($template) {
                    $emailTitle = 'Organization Updated: ' . $organization->name;
                    $headerLogo = SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png';
                    $logo = url($headerLogo);
                    $url = url('/');
                    $copyRight = SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';
                    $actionMessage = 'Your organization details have been updated on Community Resource Directory.';
                    $check_array = ['#EmailTitle#', '#Name#', '#OrganizationName#', '#ActionMessage#', '#SiteURL#', '#SiteLogo#', '#FooterCopyright#'];
                    $replace_array = [
                        $emailTitle,
                        $pointofcontacts->name,
                        $organization->name,
                        $actionMessage,
                        $url,
                        $logo,
                        $copyRight,
                    ];
                    CommonFunction::sendMail($template->id, $pointofcontacts->email, $check_array, $replace_array);
                }
            }

            // The organization index is still a Blade screen, so hand the browser
            // a hard redirect instead of an Inertia one.
            session()->flash('success', 'Organization Details updated successfully');

            return Inertia::location(route('admin.organization.index'));
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $organization = Organizations::findOrFail($id);

        try {
            $this->deleteOrganizationWithRelations($organization);
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        return back()->with('success', 'Organization details deleted successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ], [
            'ids.required' => 'Please select at least one organization.',
        ]);

        $organizations = Organizations::whereIn('id', $request->input('ids'))->get();

        if ($organizations->isEmpty()) {
            return back()->with('error', 'No matching organizations found.');
        }

        try {
            foreach ($organizations as $organization) {
                $this->deleteOrganizationWithRelations($organization);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        return back()->with('success', 'Selected organizations deleted successfully');
    }

    private function deleteOrganizationWithRelations(Organizations $organization): void
    {
        CommonFunction::fileDeleteStorage($organization->logo);

        $publications = $organization->publications()->withCount('organizations')->get();
        $organization->publications()->detach();
        foreach ($publications as $publication) {
            if (($publication->organizations_count ?? 0) <= 1) {
                CommonFunction::fileDeleteStorage($publication->image);
                CommonFunction::fileDeleteStorage($publication->file);
                $publication->delete();
            }
        }

        OrganizationDetails::where('organization_id', $organization->id)->delete();
        PointOfContacts::where('organization_id', $organization->id)->delete();
        SavedResources::where('organization_id', $organization->id)->delete();
        $organization->delete();
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate(['status' => 'required|in:active,inactive']);

        $organization = Organizations::findOrFail($id);

        // Going live pings the organization's mail server and website first, so
        // the directory does not publish a listing nobody can reach.
        if (app()->environment() !== 'local' && $request->status === 'active') {
            $failures = [];

            if (!$organization->email || !CommonFunction::smtpValidateEmail($organization->email)) {
                $failures[] = 'Organization email ping validation failed';
            }

            if (!$organization->website || !CommonFunction::validateWebsite($organization->website)) {
                $failures[] = 'Organization website ping validation failed';
            }

            if ($failures) {
                return back()->with('error', implode("\n", $failures));
            }
        }

        try {
            $organization->status = $request->status;
            $organization->save();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Status updated');
    }
    public function ExportOrganizations()
    {
        $exportArray = [];
        $maxpublication = 0;
        $organizations = Organizations::with('publications')->where('status', 'active')->orderBy('id', 'asc')->get();
        if (count($organizations) == 0) {
            return back()->with('error', 'No organizations found to export');
        }
        foreach ($organizations as $key => $organization) {
            $exportArray[$key]['Organization Name'] = $organization->name;
            $exportArray[$key]['Organization Type'] = ucwords(str_replace('-', ' ', $organization->type));
            $exportArray[$key]['Phone'] = $organization->phone;
            $exportArray[$key]['Email'] = $organization->email;
            $exportArray[$key]['Website'] = $organization->website;
            $exportArray[$key]['Service Categories'] = '';
            if ($organization->category) {
                $categories = [];
                foreach (json_decode($organization->category, true) as $category) {
                    $categories[] = Categories::find($category)->name;
                }
                $exportArray[$key]['Service Categories'] = implode(', ', $categories);
            }
            $exportArray[$key]['Target Population'] = $organization->target_population;
            $exportArray[$key]['Service Area Type'] = $organization->service_area_type;
            $exportArray[$key]['Service Area'] = $organization->service_area;
            $exportArray[$key]['Organization Logo'] = $organization->logo ? url($organization->logo) : '';

            $pointofcontacts = PointOfContacts::where('organization_id', $organization->id)->first();
            $exportArray[$key]['Point-Of-Contact Pronouns'] = $pointofcontacts?->pronouns;
            $exportArray[$key]['Point-Of-Contact First Name'] = $pointofcontacts?->first_name;
            $exportArray[$key]['Point-Of-Contact Last Name'] = $pointofcontacts?->last_name;
            $exportArray[$key]['Point-Of-Contact Email'] = $pointofcontacts?->email;
            $exportArray[$key]['Point-Of-Contact Phone'] = $pointofcontacts?->phone;
            $exportArray[$key]['Point-Of-Contact Notes'] = $pointofcontacts?->notes;
            $exportArray[$key]['Point-Of-Contact Created At'] = date('Y-m-d H:i:s', strtotime($pointofcontacts?->created_at));
            $exportArray[$key]['Point-Of-Contact Updated At'] = date('Y-m-d H:i:s', strtotime($pointofcontacts?->updated_at));

            $organization_details = OrganizationDetails::where('organization_id', $organization->id)->first();
            $exportArray[$key]['Additional Resource'] = $organization_details->additional_resource;
            $exportArray[$key]['Title'] = $organization_details->title;
            $exportArray[$key]['Description'] = $organization_details->description;
            $exportArray[$key]['File Url'] = $organization_details->file_url ? url($organization_details->file_url) : '';
            $exportArray[$key]['Source'] = $organization_details->source;
            $exportArray[$key]['Physical Address 1'] = $organization_details->physical_address_1;
            $exportArray[$key]['Physical Address 2'] = $organization_details->physical_address_2;
            $exportArray[$key]['Physical City'] = $organization_details->physical_city;
            $exportArray[$key]['Physical State'] = $organization_details->physical_state;
            $exportArray[$key]['Physical Postal Code'] = $organization_details->physical_postal_code;
            $exportArray[$key]['Mailing Address 1'] = $organization_details->mailing_address_1;
            $exportArray[$key]['Mailing Address 2'] = $organization_details->mailing_address_2;
            $exportArray[$key]['Mailing City'] = $organization_details->mailing_city;
            $exportArray[$key]['Mailing State'] = $organization_details->mailing_state;
            $exportArray[$key]['Mailing Postal Code'] = $organization_details->mailing_postal_code;
            $exportArray[$key]['Service Description'] = $organization_details->service_description;
            $social_links = json_decode($organization_details->social_links, true);
            foreach ($social_links as $socialKey => $social_link) {
                $exportArray[$key][ucfirst($socialKey)]  = $social_link;
            }
            $exportArray[$key]['Organization Created At'] = date(
                'Y-m-d H:i:s',
                strtotime($organization->created_at)
            );
            $exportArray[$key]['Organization Updated At'] = date(
                'Y-m-d H:i:s',
                strtotime(max($organization_details->updated_at, $organization->updated_at))
            );
            $publications = $organization->publications ?? collect();
            if (count($publications) >= $maxpublication) {
                $maxpublication = count($publications);
            }
            foreach ($publications as $pubKey => $publication) {
                $exportArray[$key]['Publication ' . $pubKey . ' Title'] = $publication->title;
                $exportArray[$key]['Publication ' . $pubKey . ' Description'] = $publication->description;
                $exportArray[$key]['Publication ' . $pubKey . ' File'] = $publication->file ? url($publication->file) : '';
                $exportArray[$key]['Publication ' . $pubKey . ' Image'] = $publication->image ? url($publication->image) : '';
            }
        }
        $exportHeader = OrganizationFields::where('status', 'active')->orderBy('id', 'asc')->get('name')->pluck('name')->toArray();
        for ($i = 1; $i <= $maxpublication; $i++) {
            $exportHeader[] = 'Publication ' . $i . ' Title';
            $exportHeader[] = 'Publication ' . $i . ' Description';
            $exportHeader[] = 'Publication ' . $i . ' File';
            $exportHeader[] = 'Publication ' . $i . ' Image';
        }

        // echo '<pre>';
        // print_r($exportArray);
        // exit;
        return Excel::download(new OrganizationExport($exportArray, $exportHeader), 'organizations.xlsx');
    }
    public function BulkImport()
    {
        return Inertia::render('Organizations/BulkImport', [
            'submitUrl' => route('admin.bulk-import-submit'),
            'lastImport' => $this->lastImportReport(),
        ]);
    }

    /**
     * The most recent import's outcome, or null if nothing has run.
     *
     * The file is absent on a fresh install, and file_get_contents() returns
     * false with a warning there — `?? ''` never caught it because false is not
     * null.
     *
     * @return array<string, mixed>|null
     */
    private function lastImportReport(): ?array
    {
        $path = storage_path('app/public/import/lastimported.json');

        if (!file_exists($path)) {
            return null;
        }

        $report = json_decode((string) file_get_contents($path), true);

        if (!is_array($report) || $report === []) {
            return null;
        }

        // Errors are keyed by row number and hold a list of messages each.
        $errors = [];
        foreach ($report['error'] ?? [] as $row => $messages) {
            foreach ((array) $messages as $message) {
                $errors[] = ['row' => $row, 'message' => $message];
            }
        }

        return [
            'status' => ucfirst((string) ($report['status'] ?? '')),
            'total' => $report['total'] ?? 0,
            'imported' => $report['imported'] ?? 0,
            'errors' => $errors,
        ];
    }

    public function BulkImportSubmit(Request $request)
    {
        // Was `nullable`, and the very next line dereferences the upload.
        $request->validate([
            'uploaded_file' => 'required|file|mimes:xls,xlsx,ods|max:5120',
        ]);

        {
            try {
                $file = $request->file('uploaded_file');
                $filePath = $file->storeAs(
                    'public/import',
                    pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '-' . time() . '.' . $file->getClientOriginalExtension()
                );
                $fullPath = Storage::path($filePath);
                $csvData = Excel::toArray([], $fullPath)[0];
                if (count($csvData) < 2) {
                    unlink($fullPath);
                    return back()->withErrors(['uploaded_file' => 'No records found to import.']);
                }
                $trimmedCsvData = array_map('trim', $csvData[0]);
                $fields = OrganizationFields::where('status', 'active')->get();

                $missingFields = [];
                foreach ($fields as $field) {
                    if ($field->required == 1) {
                        $positionInArray1 = array_search($field->name, $trimmedCsvData);
                        if ($positionInArray1 === false) {
                            $missingFields[] = $field->name;
                        }
                    }
                }

                if (count($missingFields) > 0) {
                    unlink($fullPath);
                    return back()->withErrors([
                        'uploaded_file' => 'The following required fields are missing: ' . implode(', ', $missingFields),
                    ]);
                }

                $totalRecords = count($csvData) - 1;
                $batchSize = 10;
                $offset = 1;
                $response = [];
                $importResponse = storage_path('app/public/import/lastimported.json');
                if (file_exists($importResponse)) {
                    $response = json_decode(file_get_contents($importResponse), true) ?? [];
                }
                if (count($response) > 0) {
                    if ($response['status'] != 'completed') {
                        unlink($fullPath);
                        return back()->withErrors([
                            'uploaded_file' => "The previous import process isn't complete yet. Please try again later.",
                        ]);
                    }
                }
                $initialresponseData = [
                    'status' => 'running',
                    'total' => 0,
                    'imported' => 0,
                    'error' => [],
                ];
                file_put_contents($importResponse, json_encode($initialresponseData));
                // if ($totalRecords > $batchSize) {
                $batchNo = 0;
                $totalBatches = ceil($totalRecords / $batchSize);
                for ($offset; $offset <= $totalRecords; $offset += $batchSize) {
                    $batchNo++;
                    ProcessImports::dispatch($fullPath, $offset, $batchSize, $batchNo, $totalBatches);
                }
                return to_route('admin.bulk-import')
                    ->with('success', 'Xlsx file data is uploaded to the server and the process is in queue.');
                // } else {
                //     $response = CommonFunction::importOrganization(array_slice($csvData, $offset, $batchSize), $trimmedCsvData, $offset);
                //     if ($response['status']) {
                //         unlink($fullPath);
                //         $response = [
                //             'status' => 'completed',
                //             'total' => $totalRecords,
                //             'imported' => $response['imported'],
                //             'error' => $response['error'],
                //         ];
                //         file_put_contents($importResponse, json_encode($response));
                //         return response()->json([
                //             'message' => 'Organization data imported successfully',
                //             'status' => 'success',
                //             'redirect' => route('admin.bulk-import')
                //         ]);
                //     } else {
                //         return response()->json([
                //             'errors' => 'An unexpected error occurred',
                //             'status' => 'error'
                //         ], 500);
                //     }
                // }
            } catch (\Exception $e) {
                return response()->json([
                    'errors' => 'An unexpected error occurred: ' . $e->getMessage(),
                    'status' => 'error'
                ], 500);
            }
        }
    }

    public function suggestedOrganizations()
    {
        return Inertia::render('SuggestedOrganizations/Index', [
            'suggestions' => SuggestOrganizations::orderBy('id', 'DESC')
                ->get()
                ->map(fn (SuggestOrganizations $suggestion) => [
                    'id' => $suggestion->id,
                    'name' => ucwords((string) $suggestion->name),
                    'email' => $suggestion->email,
                    'status' => $suggestion->status,
                    'type' => $suggestion->suggestion_type,
                    'logo' => $suggestion->logo
                        ? asset($suggestion->logo)
                        : asset('assets/img/placeholder.png'),
                    'reviewUrl' => route('admin.suggested-organizations.edit', $suggestion->id),
                ])
                ->values(),
        ]);
    }

    public function suggestedOrganizationsEdit(string $id)
    {
        $suggestion = SuggestOrganizations::findOrFail($id);

        return Inertia::render('SuggestedOrganizations/Review', [
            ...$this->formOptions(),
            'indexUrl' => route('admin.suggested-organizations.index'),
            'submitUrl' => route('admin.suggested-organizations.update', $suggestion->id),
            'suggestion' => [
                'id' => $suggestion->id,
                'type' => $suggestion->suggestion_type,
                'status' => $suggestion->status,
            ],
            // Anything already accepted or rejected is a record, not a task.
            'readonly' => $suggestion->status !== 'pending',
            'values' => $this->suggestionValues($suggestion),
            'publicationRows' => $this->suggestionPublicationRows($suggestion),
            'logoUrl' => $suggestion->logo ? asset($suggestion->logo) : '',
        ]);
    }

    /**
     * Flatten a suggestion into the same request-field names the organization
     * form posts, so the review screen reuses OrganizationForm.vue rather than
     * being a third copy of it.
     *
     * @return array<string, mixed>
     */
    private function suggestionValues(SuggestOrganizations $suggestion): array
    {
        $contact = json_decode((string) $suggestion->point_of_contact, true) ?: [];
        $details = json_decode((string) $suggestion->organization_details, true) ?: [];
        $social = $details['social_links'] ?? [];

        return [
            'organization_name' => $suggestion->name ?? '',
            'organization_type' => $suggestion->type ?? '',
            'email' => $suggestion->email ?? '',
            'phone' => preg_replace('/\D+/', '', (string) ($suggestion->phone ?? '')),
            'website' => $suggestion->website ?? '',

            'facebook' => $social['facebook'] ?? '',
            'linkedin' => $social['linkedin'] ?? '',
            'instagram' => $social['instagram'] ?? '',

            'service_description' => $details['service_description'] ?? '',
            'service_categories' => array_map('intval', json_decode((string) $suggestion->category, true) ?: []),
            'service_area' => $suggestion->service_area_type ?? '',
            'service_state' => $suggestion->service_area_type === 'state'
                ? (string) $suggestion->service_area
                : '',
            'target_population' => $suggestion->target_population ?? '',

            // The Blade form hardcoded value="" on these four, so accepting a
            // suggestion for an existing organization wiped them.
            'additional_resource' => $details['additional_resource'] ?? '',
            'title' => $details['title'] ?? '',
            'description' => $details['description'] ?? '',
            'file_url' => $details['file_url'] ?? '',

            'point_of_contact_pronouns' => $contact['pronouns'] ?? 'He/Him',
            'point_of_contact_first_name' => $contact['first_name'] ?? '',
            'point_of_contact_last_name' => $contact['last_name'] ?? '',
            'point_of_contact_email' => $contact['email'] ?? '',
            'point_of_contact_phone' => preg_replace('/\D+/', '', (string) ($contact['phone'] ?? '')),
            'point_of_contact_notes' => $contact['notes'] ?? '',

            'address_1' => $details['physical_address_1'] ?? '',
            'address_2' => $details['physical_address_2'] ?? '',
            'city' => $details['physical_city'] ?? '',
            'state' => $details['physical_state'] ?? '',
            'postcode' => $details['physical_postal_code'] ?? '',
            'latitude' => $details['latitude'] ?? '',
            'longitude' => $details['longitude'] ?? '',

            'mailing_address_1' => $details['mailing_address_1'] ?? '',
            'mailing_address_2' => $details['mailing_address_2'] ?? '',
            'mailing_city' => $details['mailing_city'] ?? '',
            'mailing_state' => $details['mailing_state'] ?? '',
            'mailing_postcode' => $details['mailing_postal_code'] ?? '',

            'source' => $details['source'] ?? '',
            'suggestion_type' => $suggestion->suggestion_type ?? 'new',
            // Carried through so accepting can copy the visitor's upload rather
            // than requiring the admin to re-pick it.
            'organization_logo_suggested' => $suggestion->logo ?? '',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function suggestionPublicationRows(SuggestOrganizations $suggestion): array
    {
        $rows = json_decode((string) $suggestion->publications, true) ?: [];

        return array_values(array_map(fn (array $row) => [
            'publication_title' => $row['publication_title'] ?? '',
            'publication_description' => $row['publication_description'] ?? '',
            'publication_state' => (string) ($row['publication_state'] ?? ''),
            'publication_update_existing' => (int) ($row['update_existing'] ?? 0),
            'cover_file' => null,
            'cover_image' => null,
            'cover_file_path' => $row['cover_file'] ?? '',
            'cover_image_path' => $row['cover_image'] ?? '',
            'cover_file_name' => '',
            'cover_image_name' => '',
        ], $rows));
    }

    public function suggestedOrganizationsUpdate(Request $request, string $id)
    {
        $suggestedOrganization = SuggestOrganizations::findOrFail($id);

        if ($suggestedOrganization->status !== 'pending') {
            return back()->with('error', 'This suggestion has already been reviewed.');
        }

        // Rejection is decided before validation runs. A suggestion is often
        // rejected *because* its fields are junk, and the old order made those
        // exact suggestions impossible to throw out.
        if ($request->form_type === 'rejected') {
            $suggestedOrganization->status = 'rejected';
            $suggestedOrganization->save();

            $this->notifySuggester($suggestedOrganization, 4);

            return to_route('admin.suggested-organizations.index')
                ->with('success', 'Organization Details suggestion rejected successfully');
        }

        $request->validate(
            // Same rules as the organization form, except `file_url`: the admin
            // form uploads a file there, a suggestion carries a link.
            ['file_url' => 'nullable|url'] + $this->validationRules($request),
            $this->validationMessages(),
        );

        // The accept path below is the original implementation, unchanged. The
        // bare blocks stand in for the `else` branches that used to wrap it so
        // its indentation and brace depth stay exactly as they were.
        {
            try {
                {
                    $service_area = $request->service_state ?? '';
                    if (!empty($request->service_area) && $request->service_area == 'local') {
                        $service_area = $request->state;
                    } elseif (!empty($request->service_area) && $request->service_area == 'national') {
                        $service_area = 'National';
                    }

                    // Organization Details Add/Update
                    $organization = $suggestedOrganization->suggestion_type == 'new' ? new Organizations() : Organizations::find($suggestedOrganization->organization_id);
                    $organization->name = str_replace('`', '\'', $request->organization_name);
                    $organization->type = $request->organization_type;
                    $organization->phone = $request->phone;
                    $organization->email = $request->email;
                    $organization->website = $request->website;
                    $organization->category = json_encode($request->service_categories);
                    $organization->target_population = $request->target_population;
                    $organization->service_area_type = $request->service_area;
                    $organization->service_area = $service_area;
                    $organization->save();
                    if ($request->hasFile('organization_logo')) {
                        $file = CommonFunction::fileUploadStorage($request->file('organization_logo'), 'organization', $organization->id . '-organization');
                        if (!empty($file)) {
                            if ($organization->logo) {
                                CommonFunction::fileDeleteStorage($organization->logo);
                            }
                            $organization->logo = $file;
                        }
                    } elseif ($request->organization_logo_suggested != '') {
                        $file = Commonfunction::moveFileWithRename($request->organization_logo_suggested, 'organization', $organization->id . '-organization');
                        if ($file) {
                            if ($organization->logo) {
                                CommonFunction::fileDeleteStorage($organization->logo);
                            }
                            $organization->logo = $file;
                        }
                    }
                    $organization->save();

                    if ($suggestedOrganization->suggestion_type != 'new') {
                        // An existing organization may predate either row.
                        $pointOfContactId = PointOfContacts::where('organization_id', $organization->id)->value('id');
                        $organizationDetailsId = OrganizationDetails::where('organization_id', $organization->id)->value('id');

                        $oldPublications = $organization->publications()->withCount('organizations')->get();
                        $incomingTitles = [];
                        foreach (($request->publication_title ?? []) as $pubKey => $title) {
                            $title = trim((string) $title);
                            $description = trim((string) ($request->publication_description[$pubKey] ?? ''));
                            if ($title === '' || $description === '') {
                                continue;
                            }
                            $incomingTitles[] = $title;
                        }
                        $incomingTitleMap = array_flip(array_map('strtolower', $incomingTitles));
                        $organization->publications()->detach();
                        foreach ($oldPublications as $publication) {
                            $shouldPreserve = isset($incomingTitleMap[strtolower((string) $publication->title)]);
                            if ($shouldPreserve) {
                                continue;
                            }
                            if (($publication->organizations_count ?? 0) <= 1) {
                                $publicationController = new PublicationController();
                                $publicationController->destroy($publication->id);
                            }
                        }
                    }

                    if (count($request->publication_title ?? []) > 0) {
                        foreach ($request->publication_title as $pubKey => $publication_title) {
                            $publication_title = trim((string) $publication_title);
                            $publication_description = trim((string) ($request->publication_description[$pubKey] ?? ''));
                            if ($publication_title === '' || $publication_description === '') {
                                continue;
                            }
                            $stateValue = $request->publication_state[$pubKey] ?? null;
                            $state = null;
                            if (!empty($stateValue) && $stateValue !== 'national') {
                                $state = States::where('id', $stateValue)->first();
                            }
                            $updateExisting = ($suggestedOrganization->suggestion_type != 'new');
                            if (!empty($request->publication_update_existing[$pubKey])) {
                                $updateExisting = true;
                            }

                            $existingPublication = Publications::where('title', $publication_title)->first();
                            if ($updateExisting && $existingPublication) {
                                $publication = $existingPublication;
                                $publication->state = ($stateValue === 'national') ? 'national' : ($state ? $state->id : $publication->state);
                                $publication->description = $publication_description;
                                $publication->status = 'active';
                                if (isset($request->cover_file[$pubKey])) {
                                    $file = CommonFunction::fileUploadStorage($request->cover_file[$pubKey], 'publictions', $publication->id . '-file-');
                                    if (!empty($file)) {
                                        CommonFunction::fileDeleteStorage($publication->file);
                                        $publication->file = $file;
                                    }
                                } elseif (($request->cover_file_path[$pubKey] ?? '') != '') {
                                    $file = Commonfunction::moveFileWithRename($request->cover_file_path[$pubKey] ?? '', 'publictions', $publication->id . '-file-');
                                    if ($file) {
                                        CommonFunction::fileDeleteStorage($publication->file);
                                        $publication->file = $file;
                                    }
                                }
                                if (isset($request->cover_image[$pubKey])) {
                                    $image = CommonFunction::fileUploadStorage($request->cover_image[$pubKey], 'publictions', $publication->id . '-image-');
                                    if (!empty($image)) {
                                        CommonFunction::fileDeleteStorage($publication->image);
                                        $publication->image = $image;
                                    }
                                } elseif (($request->cover_image_path[$pubKey] ?? '') != '') {
                                    $image = Commonfunction::moveFileWithRename($request->cover_image_path[$pubKey] ?? '', 'publictions', $publication->id . '-image-');
                                    if ($image) {
                                        CommonFunction::fileDeleteStorage($publication->image);
                                        $publication->image = $image;
                                    }
                                }
                                $publication->save();
                            } else {
                                $publication = new Publications();
                                $publication->title = $publication_title;
                                $publication->state = ($stateValue === 'national') ? 'national' : ($state ? $state->id : null);
                                $publication->description = $publication_description;
                                $publication->status = 'active';
                                $publication->save();
                                if (isset($request->cover_file[$pubKey])) {
                                    $file = CommonFunction::fileUploadStorage($request->cover_file[$pubKey], 'publictions', $publication->id . '-file-');
                                    if (!empty($file)) {
                                        $publication->file = $file;
                                    }
                                } elseif (($request->cover_file_path[$pubKey] ?? '') != '') {
                                    $file = Commonfunction::moveFileWithRename($request->cover_file_path[$pubKey] ?? '', 'publictions', $publication->id . '-file-');
                                    if ($file) {
                                        $publication->file = $file;
                                    }
                                }
                                if (isset($request->cover_image[$pubKey])) {
                                    $image = CommonFunction::fileUploadStorage($request->cover_image[$pubKey], 'publictions', $publication->id . '-image-');
                                    if (!empty($image)) {
                                        $publication->image = $image;
                                    }
                                } elseif (($request->cover_image_path[$pubKey] ?? '') != '') {
                                    $image = Commonfunction::moveFileWithRename($request->cover_image_path[$pubKey] ?? '', 'publictions', $publication->id . '-image-');
                                    if ($image) {
                                        $publication->image = $image;
                                    }
                                }
                                $publication->save();
                            }
                            $publication->organizations()->syncWithoutDetaching([$organization->id]);
                        }
                    }

                    $pointofcontacts = ($suggestedOrganization->suggestion_type == 'new' || !$pointOfContactId)
                        ? new PointOfContacts()
                        : PointOfContacts::find($pointOfContactId);
                    $pointofcontacts->organization_id = $organization->id;
                    $pointofcontacts->pronouns = $request->point_of_contact_pronouns;
                    $pointofcontacts->first_name = $request->point_of_contact_first_name;
                    $pointofcontacts->last_name = $request->point_of_contact_last_name;
                    $pointofcontacts->name = $request->point_of_contact_first_name . ' ' . $request->point_of_contact_last_name;
                    $pointofcontacts->email = $request->point_of_contact_email;
                    $pointofcontacts->phone = $request->point_of_contact_phone;
                    $pointofcontacts->notes = $request->point_of_contact_notes;
                    $pointofcontacts->save();

                    $organization_details = ($suggestedOrganization->suggestion_type == 'new' || !$organizationDetailsId)
                        ? new OrganizationDetails()
                        : OrganizationDetails::find($organizationDetailsId);
                    $organization_details->organization_id = $organization->id;
                    $organization_details->additional_resource = $request->additional_resource;
                    $organization_details->title = $request->title;
                    $organization_details->description = $request->description;
                    $organization_details->file_url = $request->file_url;
                    $organization_details->source = $request->source;
                    $organization_details->physical_address_1 = $request->address_1;

                    $organization_details->latitude = $request->latitude;
                    $organization_details->longitude = $request->longitude;

                    $organization_details->physical_address_2 = $request->address_2;
                    $organization_details->physical_city = $request->city;
                    $organization_details->physical_state = $request->state;
                    $organization_details->physical_postal_code = $request->postcode;
                    $organization_details->mailing_address_1 = $request->mailing_address_1;
                    $organization_details->mailing_address_2 = $request->mailing_address_2;
                    $organization_details->mailing_city = $request->mailing_city;
                    $organization_details->mailing_state = $request->mailing_state;
                    $organization_details->mailing_postal_code = $request->mailing_postcode;
                    $organization_details->service_description = $request->service_description;
                    $social_links = [
                        'facebook' => $request->facebook,
                        'linkedin' => $request->linkedin,
                        'instagram' => $request->instagram,
                    ];
                    $organization_details->social_links = json_encode($social_links);
                    $organization_details->save();

                    $suggestedOrganization->status = 'accepted';
                    $suggestedOrganization->name = str_replace('`', '\'', $request->organization_name);
                    $suggestedOrganization->type = $request->organization_type;
                    $suggestedOrganization->phone = $request->phone;
                    $suggestedOrganization->email = $request->email;
                    $suggestedOrganization->website = $request->website;
                    $suggestedOrganization->category = json_encode($request->service_categories);
                    $suggestedOrganization->target_population = $request->target_population;
                    $suggestedOrganization->service_area_type = $request->service_area;
                    $suggestedOrganization->service_area = $service_area;
                    $suggestedOrganization->logo = $organization->logo;
                    $suggestedOrganization->save();
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
                    $oldPublicationJsonDetails = json_decode($suggestedOrganization->publications ?? '', true) ?? [];
                    foreach ($oldPublicationJsonDetails as $oldPublication) {
                        if ($oldPublication['cover_file']) {
                            CommonFunction::fileDeleteStorage($oldPublication['cover_file']);
                        }
                    }
                    $publications = $organization->publications ?? collect();
                    foreach ($publications ?? [] as $pubKey => $publication) {
                        $publicationDetails[$pubKey] = [
                            "publication_title" => $publication->title,
                            "publication_description" => $publication->description,
                            "cover_file" => $publication->file,
                        ];
                    }
                    $suggestedOrganization->publications = json_encode($publicationDetails);
                    $suggestedOrganization->save();

                    // Notify the point of contact when a suggestion is accepted
                    if ($pointofcontacts && $pointofcontacts->email) {
                        $template = EmailTemplates::find(7);
                        if ($template) {
                            $emailTitle = $suggestedOrganization->suggestion_type === 'new'
                                ? 'Organization Added: ' . $organization->name
                                : 'Organization Updated: ' . $organization->name;
                            $headerLogo = SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png';
                            $logo = url($headerLogo);
                            $url = url('/');
                            $copyRight = SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';
                            $actionMessage = $suggestedOrganization->suggestion_type === 'new'
                                ? 'Your organization has been added to the Community Resource Directory directory.'
                                : 'Your organization details have been updated on Community Resource Directory.';
                            $check_array = ['#EmailTitle#', '#Name#', '#OrganizationName#', '#ActionMessage#', '#SiteURL#', '#SiteLogo#', '#FooterCopyright#'];
                            $replace_array = [
                                $emailTitle,
                                $pointofcontacts->name,
                                $organization->name,
                                $actionMessage,
                                $url,
                                $logo,
                                $copyRight,
                            ];
                            CommonFunction::sendMail($template->id, $pointofcontacts->email, $check_array, $replace_array);
                        }
                    }
                }

                $this->notifySuggester($suggestedOrganization, 3);
            } catch (\Exception $e) {
                return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
            }
        }

        return to_route('admin.suggested-organizations.index')
            ->with('success', 'Organization Details suggestion accepted successfully');
    }

    /**
     * Tell whoever submitted the suggestion what happened to it.
     */
    private function notifySuggester(SuggestOrganizations $suggestion, int $template): void
    {
        $user = User::find($suggestion->user_id);

        if (!$user) {
            return;
        }

        $headerLogo = SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png';
        $copyRight = SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';

        try {
            CommonFunction::sendMail(
                $template,
                $user->email,
                ['#Name#', '#SiteURL#', '#SiteLogo#', '#FooterCopyright#'],
                [$user->first_name, url('/'), url($headerLogo), $copyRight],
            );
        } catch (\Throwable $e) {
            // The decision is already recorded. A missing template or an SMTP
            // outage must not leave the admin thinking the review failed.
            report($e);
        }
    }
}
