<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\CommonFunction;
use App\Http\Controllers\Controller;
use App\Models\Organizations;
use App\Models\Publications;
use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class AdminRouteController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                [
                    'label' => 'Users',
                    'value' => User::where('role', '!=', 'admin')->count(),
                    'tone' => 'primary',
                    'icon' => 'users',
                    'url' => route('admin.user.index'),
                ],
                [
                    'label' => 'Organizations',
                    'value' => Organizations::where('status', 'active')->count(),
                    'tone' => 'success',
                    'icon' => 'clipboard',
                    'url' => route('admin.organization.index'),
                ],
                [
                    'label' => 'Publications',
                    'value' => Publications::where('status', 'active')->count(),
                    'tone' => 'success',
                    'icon' => 'clipboard',
                    'url' => route('admin.publication.index'),
                ],
            ],
        ]);
    }

    public function generalSettings()
    {
        $settings = SiteSettings::pluck('settings_value', 'settings_name');

        return Inertia::render('Admin/Settings', [
            'submitUrl' => route('admin.saveSettings'),
            'clearCacheUrl' => route('admin.clear-cache'),
            'ckeditorUrl' => asset('plugins/ckeditor/ckeditor.js'),
            'values' => [
                'website_name' => $settings['website_name'] ?? '',
                'site_title' => $settings['site_title'] ?? '',
                'meta_title' => $settings['meta_title'] ?? '',
                'meta_keywords' => $settings['meta_keywords'] ?? '',
                'meta_description' => $settings['meta_description'] ?? '',
                'copy_right' => $settings['copy_right'] ?? '',
                'footer_description' => $settings['footer_description'] ?? '',
                'footer_hq_address' => $settings['footer_hq_address'] ?? '',
                'footer_hq_phone_hours' => $settings['footer_hq_phone_hours'] ?? '',
                'footer_mailing_address' => $settings['footer_mailing_address'] ?? '',
                'footer_mailing_phone_hours' => $settings['footer_mailing_phone_hours'] ?? '',
                'admin_email' => $settings['admin_email'] ?? '',
            ],
            // Stored as JSON arrays; always handed over as a list with at least
            // one row so the form has something to render.
            'lists' => [
                'footer_hq_phone' => $this->settingList($settings['footer_hq_phone'] ?? null),
                'footer_hq_email' => $this->settingList($settings['footer_hq_email'] ?? null),
                'footer_mailing_phone' => $this->settingList($settings['footer_mailing_phone'] ?? null),
                'footer_mailing_email' => $this->settingList($settings['footer_mailing_email'] ?? null),
            ],
            'logos' => [
                'header' => !empty($settings['header_logo']) ? asset($settings['header_logo']) : '',
                'footer' => !empty($settings['footer_logo']) ? asset($settings['footer_logo']) : '',
            ],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function settingList(mixed $raw): array
    {
        $values = is_array($raw) ? $raw : json_decode((string) $raw, true);
        $values = is_array($values) ? array_values(array_filter($values, fn ($v) => $v !== null && $v !== '')) : [];

        return $values ?: [''];
    }

    /**
     * The settings this form owns.
     *
     * saveSettings() used to write whatever `key[...]` names arrived, so a
     * crafted post could create or overwrite any row in the table — including
     * `asset_version`, which the cache-busting logic depends on.
     *
     * @var array<int, string>
     */
    private const SCALAR_SETTINGS = [
        'website_name',
        'site_title',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'copy_right',
        'footer_description',
        'footer_hq_address',
        'footer_hq_phone_hours',
        'footer_mailing_address',
        'footer_mailing_phone_hours',
        'admin_email',
    ];

    /** @var array<int, string> */
    private const LIST_SETTINGS = [
        'footer_hq_phone',
        'footer_hq_email',
        'footer_mailing_phone',
        'footer_mailing_email',
    ];

    public function saveSettings(Request $request)
    {
        $request->validate([
            'key.website_name' => 'required|string|max:255',
            'key.site_title' => 'nullable|string|max:255',
            'key.meta_title' => 'nullable|string|max:255',
            'key.meta_keywords' => 'nullable|string|max:2000',
            'key.meta_description' => 'nullable|string|max:2000',
            // The contact form mails this address; an invalid value fails silently.
            'key.admin_email' => 'required|email|max:255',
            'key.footer_hq_email.*' => 'nullable|email|max:255',
            'key.footer_mailing_email.*' => 'nullable|email|max:255',
            'header_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'footer_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
        ], [
            'key.website_name.required' => 'Please enter the website name.',
            'key.admin_email.required' => 'Please enter the admin email address.',
            'key.admin_email.email' => 'Please enter a valid admin email address.',
        ]);

        try {
            $posted = $request->input('key', []);

            foreach (self::SCALAR_SETTINGS as $name) {
                if (array_key_exists($name, $posted)) {
                    $this->putSetting($name, (string) $posted[$name]);
                }
            }

            foreach (self::LIST_SETTINGS as $name) {
                if (!array_key_exists($name, $posted)) {
                    continue;
                }

                $values = array_values(array_filter(
                    (array) $posted[$name],
                    fn ($item) => $item !== null && $item !== '',
                ));

                $this->putSetting($name, json_encode($values));
            }

            $this->replaceLogo($request, 'header_logo', 'site-logo');
            $this->replaceLogo($request, 'footer_logo', 'footer-logo');

            return to_route('admin.generalSettings')->with('success', 'Settings successfully updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings. ' . $e->getMessage());
        }
    }

    private function putSetting(string $name, string $value): void
    {
        SiteSettings::updateOrInsert(
            ['settings_name' => $name],
            ['settings_value' => $value, 'updated_at' => now()],
        );
    }

    /**
     * Swap one of the two site logos, deleting whatever it replaces.
     */
    private function replaceLogo(Request $request, string $field, string $prefix): void
    {
        if (!$request->hasFile($field)) {
            return;
        }

        $existing = SiteSettings::where('settings_name', $field)->value('settings_value');
        $path = CommonFunction::fileUploadStorage($request->file($field), 'settings', $prefix);

        if (empty($path)) {
            return;
        }

        if (!empty($existing)) {
            CommonFunction::fileDeleteStorage($existing);
        }

        $this->putSetting($field, $path);
    }

    public function clearCacheAndVersion()
    {
        try {
            $previousVersion = SiteSettings::where('settings_name', 'asset_version')->value('settings_value') ?? config('custom.app_version', '1.0.0');
            $versionParts = array_map('intval', explode('.', $previousVersion));

            // Ensure we always have major.minor.patch
            if (count($versionParts) < 3) {
                $versionParts = array_pad($versionParts, 3, 0);
            }
            $lastIndex = count($versionParts) - 1;
            $versionParts[$lastIndex]++;

            if ($versionParts[$lastIndex] > 9) {
                $versionParts[$lastIndex] = 0;
                $midIndex = $lastIndex - 1;
                $versionParts[$midIndex]++;

                if ($versionParts[$midIndex] > 9 && $midIndex > 0) {
                    $versionParts[$midIndex] = 0;
                    $majorIndex = $midIndex - 1;
                    $versionParts[$majorIndex]++;
                }
            }
            $version = implode('.', $versionParts);

            if (SiteSettings::where('settings_name', '=', 'asset_version')->count() > 0) {
                SiteSettings::where('settings_name', 'asset_version')->update([
                    'settings_value' => $version,
                    'updated_at' => now(),
                ]);
            } else {
                SiteSettings::insert([
                    'settings_name' => 'asset_version',
                    'settings_value' => $version,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            cache()->forget('asset_version');
            return back()->with('success', 'Frontend cache has been cleared.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function viewProfile()
    {
        $admin = auth()->user();

        return Inertia::render('Admin/Profile', [
            'submitUrl' => route('admin.profile.update'),
            'values' => [
                'first_name' => $admin->first_name ?? '',
                'last_name' => $admin->last_name ?? '',
                'email' => $admin->email ?? '',
                'phone' => preg_replace('/\D+/', '', (string) ($admin->phone ?? '')),
            ],
            'avatarUrl' => asset(
                !empty($admin->profile_pic) ? $admin->profile_pic : '/backend/assets/img/avatars/avatar.jpg',
            ),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $rules = [
            'first_name' => 'required',
            'email' => 'required|email',
            'phone' => 'nullable',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
        if (!empty($request->reset_password)) {
            $rules['current_password'] = 'required|min:8|max:20';
            $rules['password'] = 'required|min:8|max:20|confirmed';
        }
        $request->validate($rules);
        $user = auth()->user();
        $admin = User::find($user->id);

        if (!empty($request->reset_password)) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->with('error', 'Unable to verify current password.');
            }
            $admin->password = Hash::make($request->password);
        }
        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        if ($request->hasFile('profile_pic')) {
            $file = CommonFunction::fileUploadStorage($request->file('profile_pic'), 'profile', $admin->first_name . ' ' . $admin->last_name);
            if (!empty($file)) {
                CommonFunction::fileDeleteStorage($admin->profile_pic);
                $admin->profile_pic = $file;
            }
        }
        $admin->save();

        return back()->with('success', 'Profile updated successfully.');
    }


    public function emailCheck()
    {
        $email = "coder.web@yahoo.com";
        echo CommonFunction::smtpValidateEmail($email);
    }
    public function validateWebsite()
    {
        $result = CommonFunction::validateWebsite('https://packagist.org/');

        echo '<br>';
        print_r($result);
    }
}
