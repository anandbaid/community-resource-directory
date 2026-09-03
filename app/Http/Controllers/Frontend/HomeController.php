<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\CommonFunction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Frontend\Concerns\SharesAccountNavigation;
use App\Models\Banners;
use App\Models\Categories;
use App\Models\Organizations;
use App\Models\States;
use App\Models\SuggestOrganizations;
use App\Models\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SavedResources;
use App\Models\SavedSearchResources;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class HomeController extends Controller
{
    use SharesAccountNavigation;

    public function home()
    {
        $states = States::orderBy('name', 'ASC')->get();
        $categories = Categories::orderBy('category_order', 'ASC')->get();
        $location_array = Organizations::leftJoin('organization_details', 'organization_details.organization_id', 'organizations.id')
            ->where('organizations.status', 'active')
            ->select('organizations.name as name', 'organization_details.latitude as lat', 'organization_details.longitude as lng', 'organization_details.physical_state as state', 'physical_postal_code as postal_code', 'organizations.id as org_id', 'organizations.type as type')
            ->orderBy('organizations.id', 'desc')
            ->get()
            ->toArray();

        $banners = Banners::where('page_slug', 'home')->where('status', 'active')->orderBy('order', 'asc')->get();
        $homeSettings = SiteSettings::whereIn('settings_name', [
            'home_resource_block_1',
            'home_resource_block_2',
            'home_what_we_do_block',
            'home_what_we_do_item_1_title',
            'home_what_we_do_item_1_desc',
            'home_what_we_do_item_1_image',
            'home_what_we_do_item_1_icon',
            'home_what_we_do_item_1_link',
            'home_what_we_do_item_2_title',
            'home_what_we_do_item_2_desc',
            'home_what_we_do_item_2_image',
            'home_what_we_do_item_2_icon',
            'home_what_we_do_item_2_link',
            'home_what_we_do_item_3_title',
            'home_what_we_do_item_3_desc',
            'home_what_we_do_item_3_image',
            'home_what_we_do_item_3_icon',
            'home_what_we_do_item_3_link',
            'home_what_we_do_item_4_title',
            'home_what_we_do_item_4_desc',
            'home_what_we_do_item_4_image',
            'home_what_we_do_item_4_icon',
            'home_what_we_do_item_4_link',
            'home_shop_block',
            'home_about_block',
            'home_about_image',
            'home_career_success_hub_block',
            'home_career_success_hub_icon_1_title',
            'home_career_success_hub_icon_1_image',
            'home_career_success_hub_icon_2_title',
            'home_career_success_hub_icon_2_image',
            'home_career_success_hub_icon_3_title',
            'home_career_success_hub_icon_3_image',
            'home_career_success_hub_icon_4_title',
            'home_career_success_hub_icon_4_image',
            'home_career_success_hub_image',
            'home_support_block',
        ])->pluck('settings_value', 'settings_name')->toArray();
        return view('frontend.index', compact('banners', 'states', 'categories', 'location_array', 'homeSettings'));
    }

    public function dashboard()
    {
        $user = auth()->user();

        return Inertia::render('Account/Dashboard', [
            'quickLinks' => $this->quickLinks(),
            'bannerImage' => asset('assets/img/banner.png'),
            'user' => $this->userSummary($user),
            'counts' => $this->accountCounts($user),
            'links' => [
                'profile' => route('user.profile'),
                'savedResources' => url('saved-resources-view'),
                'savedSearches' => url('saved-search-view'),
                'suggestNew' => url('suggest-new-resources'),
            ],
            'suggestedOrganizations' => SuggestOrganizations::where('user_id', $user->id)
                ->orderBy('id', 'DESC')
                ->get()
                ->map(fn (SuggestOrganizations $suggestion) => [
                    'id' => $suggestion->id,
                    'name' => $suggestion->name ?: 'Untitled organization',
                    'suggestionType' => ucfirst($suggestion->suggestion_type ?? 'new'),
                    'status' => ucfirst($suggestion->status ?? 'pending'),
                    'type' => $suggestion->type == 'government' ? 'Government' : 'Non-Government',
                    'createdAt' => optional($suggestion->created_at)->format('M j, Y'),
                    'website' => $suggestion->website,
                    'organizationUrl' => $suggestion->organization_id
                        ? url('/organization-details/' . $suggestion->organization_id)
                        : null,
                ])
                ->values(),
        ]);
    }

    public function profile()
    {
        $user = auth()->user();

        return Inertia::render('Account/Profile', [
            'quickLinks' => $this->quickLinks(),
            'bannerImage' => asset('assets/img/banner.png'),
            'dashboardUrl' => route('user.dashboard'),
            'submitUrl' => route('user.profile.update'),
            'user' => $this->userSummary($user),
            'counts' => $this->accountCounts($user),
            'values' => [
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                // Stored and validated as bare digits; the Vue input masks it.
                'phone' => preg_replace('/\D+/', '', (string) $user->phone),
                'zipcode' => $user->zipcode ?? '',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userSummary($user): array
    {
        return [
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phoneFormatted' => $user->phone ? CommonFunction::formatPhone($user->phone) : '',
            'zipcode' => $user->zipcode,
            'status' => $user->status ?? 'active',
            'memberSince' => optional($user->created_at)->format('F j, Y'),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function accountCounts($user): array
    {
        return [
            'savedResources' => SavedResources::where('user_id', $user->id)->count(),
            'savedSearches' => SavedSearchResources::where('user_id', $user->id)->count(),
        ];
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|regex:/^[0-9]{10,20}$/',
            'zipcode' => 'nullable|regex:/^\\d{5}(-\\d{4})?$/',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->name = trim($request->first_name . ' ' . $request->last_name);
        $user->phone = $request->phone;
        $user->zipcode = $request->zipcode;
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}
