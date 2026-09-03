<?php

namespace Tests\Feature;

use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The admin dashboard, general settings, profile and login.
 *
 * saveSettings() used to write whatever `key[...]` names arrived, so the tests
 * that pin the allowed set are the point of this file as much as the rendering.
 */
class AdminChromeVueTest extends TestCase
{
    /** @var array<int, string> */
    private array $touchedSettings = [];

    private ?User $admin = null;

    protected function tearDown(): void
    {
        foreach ($this->touchedSettings as $name) {
            SiteSettings::where('settings_name', $name)->delete();
        }

        $this->touchedSettings = [];
        $this->admin = null;

        User::where('email', 'like', 'chrome-smoke-%@example.com')->delete();

        parent::tearDown();
    }

    private function admin(string $password = 'Original-Passw0rd!'): User
    {
        if ($this->admin) {
            return $this->admin;
        }

        $user = new User();
        $user->first_name = 'Chrome';
        $user->last_name = 'Admin';
        $user->name = 'Chrome Admin';
        $user->email = 'chrome-smoke-admin@example.com';
        $user->role = 'admin';
        $user->status = 'active';
        $user->password = Hash::make($password);
        $user->email_verified_at = now();
        $user->save();

        return $this->admin = $user;
    }

    private function member(): User
    {
        $user = new User();
        $user->first_name = 'Chrome';
        $user->last_name = 'Member';
        $user->name = 'Chrome Member';
        $user->email = 'chrome-smoke-member@example.com';
        $user->role = 'user';
        $user->status = 'active';
        $user->password = Hash::make('Original-Passw0rd!');
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    /** The minimum the settings form requires, so posts do not fail on it. */
    private function settingsPayload(array $overrides = []): array
    {
        return ['key' => array_merge([
            'website_name' => 'Chrome Smoke Site',
            'admin_email' => 'chrome-smoke-site@example.com',
        ], $overrides)];
    }

    private function remember(string ...$names): void
    {
        foreach ($names as $name) {
            if (!SiteSettings::where('settings_name', $name)->exists()) {
                $this->touchedSettings[] = $name;
            }
        }
    }

    private function unguarded()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_the_dashboard_renders_its_counts(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Admin\\/Dashboard', false);

        $stats = $this->pageProps($response->getContent())['stats'];

        $this->assertCount(3, $stats);
        $this->assertSame(['Users', 'Organizations', 'Publications'], array_column($stats, 'label'));

        foreach ($stats as $stat) {
            $this->assertIsInt($stat['value']);
            $this->assertNotSame('', $stat['url']);
        }
    }

    public function test_the_settings_screen_renders_its_values_and_lists(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/general-settings');

        $response->assertOk();
        $response->assertSee('Admin\\/Settings', false);

        $props = $this->pageProps($response->getContent());

        $this->assertArrayHasKey('website_name', $props['values']);
        $this->assertArrayHasKey('admin_email', $props['values']);

        // Every repeatable list arrives with at least one row to render.
        foreach (['footer_hq_phone', 'footer_hq_email', 'footer_mailing_phone', 'footer_mailing_email'] as $list) {
            $this->assertNotEmpty($props['lists'][$list], "List [{$list}] should never be empty.");
        }
    }

    public function test_saving_settings_stores_scalars_and_lists(): void
    {
        $this->remember('website_name', 'admin_email', 'footer_hq_phone', 'footer_mailing_phone');

        $this->unguarded()
            ->actingAs($this->admin())
            ->post('/admin/save-settings', $this->settingsPayload([
                'footer_hq_phone' => ['5551112222', '', '5553334444'],
                'footer_mailing_phone' => ['5559998888'],
            ]))
            ->assertRedirect('/admin/general-settings');

        $this->assertSame('Settings successfully updated.', session('success'));

        $this->assertSame(
            'Chrome Smoke Site',
            SiteSettings::where('settings_name', 'website_name')->value('settings_value'),
        );

        // Blanks are dropped before the list is stored.
        $this->assertSame(
            ['5551112222', '5553334444'],
            json_decode(SiteSettings::where('settings_name', 'footer_hq_phone')->value('settings_value'), true),
        );
    }

    /**
     * The HQ and mailing lists shared a container id in the Blade view, so
     * "Add Phone" under Mailing appended to the HQ block. They are separate
     * arrays now — this pins that they round-trip independently.
     */
    public function test_the_headquarters_and_mailing_lists_stay_separate(): void
    {
        $this->remember('website_name', 'admin_email', 'footer_hq_phone', 'footer_mailing_phone');

        $this->unguarded()
            ->actingAs($this->admin())
            ->post('/admin/save-settings', $this->settingsPayload([
                'footer_hq_phone' => ['1111111111'],
                'footer_mailing_phone' => ['2222222222'],
            ]));

        $props = $this->pageProps(
            $this->actingAs($this->admin())->get('/admin/general-settings')->getContent(),
        );

        $this->assertSame(['1111111111'], $props['lists']['footer_hq_phone']);
        $this->assertSame(['2222222222'], $props['lists']['footer_mailing_phone']);
    }

    /** Any `key[...]` name used to become a settings row. */
    public function test_saving_settings_ignores_unknown_keys(): void
    {
        $this->remember('website_name', 'admin_email');

        $this->unguarded()
            ->actingAs($this->admin())
            ->post('/admin/save-settings', $this->settingsPayload([
                'asset_version' => '99.99.99',
                'totally_made_up' => 'nope',
            ]))
            ->assertRedirect('/admin/general-settings');

        $this->assertNull(SiteSettings::where('settings_name', 'totally_made_up')->value('settings_value'));
        $this->assertNotSame(
            '99.99.99',
            SiteSettings::where('settings_name', 'asset_version')->value('settings_value'),
        );
    }

    public function test_saving_settings_validates_the_admin_email(): void
    {
        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/general-settings')
            ->post('/admin/save-settings', $this->settingsPayload([
                'website_name' => '',
                'admin_email' => 'not-an-email',
            ]))
            ->assertRedirect('/admin/general-settings')
            ->assertSessionHasErrors(['key.website_name', 'key.admin_email']);
    }

    public function test_the_profile_screen_renders_the_signed_in_admin(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/profile');

        $response->assertOk();
        $response->assertSee('Admin\\/Profile', false);

        $props = $this->pageProps($response->getContent());

        $this->assertSame('Chrome', $props['values']['first_name']);
        $this->assertSame($admin->email, $props['values']['email']);
        $this->assertNotSame('', $props['avatarUrl']);
    }

    public function test_updating_the_profile_redirects_with_flash(): void
    {
        $admin = $this->admin();

        $this->unguarded()
            ->actingAs($admin)
            ->from('/admin/profile')
            ->post('/admin/profile', [
                'first_name' => 'Renamed',
                'last_name' => 'Person',
                'email' => $admin->email,
                'phone' => '5559998888',
            ])
            ->assertRedirect('/admin/profile');

        $this->assertSame('Profile updated successfully.', session('success'));

        $admin->refresh();
        $this->assertSame('Renamed', $admin->first_name);
        $this->assertSame('5559998888', $admin->phone);
    }

    /** The password rules only apply when the admin opted in. */
    public function test_changing_the_password_requires_the_current_one(): void
    {
        $admin = $this->admin();

        $this->unguarded()
            ->actingAs($admin)
            ->from('/admin/profile')
            ->post('/admin/profile', [
                'first_name' => 'Chrome',
                'email' => $admin->email,
                'reset_password' => 1,
                'current_password' => 'Wrong-Passw0rd!',
                'password' => 'Brand-New-Passw0rd!',
                'password_confirmation' => 'Brand-New-Passw0rd!',
            ])
            ->assertRedirect('/admin/profile');

        $this->assertSame('Unable to verify current password.', session('error'));

        $admin->refresh();
        $this->assertTrue(Hash::check('Original-Passw0rd!', $admin->password));
    }

    public function test_changing_the_password_works_with_the_current_one(): void
    {
        $admin = $this->admin();

        $this->unguarded()
            ->actingAs($admin)
            ->post('/admin/profile', [
                'first_name' => 'Chrome',
                'email' => $admin->email,
                'reset_password' => 1,
                'current_password' => 'Original-Passw0rd!',
                'password' => 'Brand-New-Passw0rd!',
                'password_confirmation' => 'Brand-New-Passw0rd!',
            ]);

        $admin->refresh();
        $this->assertTrue(Hash::check('Brand-New-Passw0rd!', $admin->password));
    }

    public function test_the_admin_login_renders_without_the_authenticated_chrome(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSee('Auth\\/AdminLogin', false);

        // The sidebar is built from the signed-in user and has nothing to show.
        $response->assertDontSee('app-sidebar', false);
    }

    public function test_an_admin_signs_in_and_is_handed_to_the_dashboard(): void
    {
        $admin = $this->admin();

        $response = $this->unguarded()
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/admin/login', [
                'administrator_email' => $admin->email,
                'administrator_password' => 'Original-Passw0rd!',
            ]);

        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', url('/admin/dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    /** Same open redirect as the public login, on the admin session. */
    public function test_the_admin_login_refuses_an_offsite_redirect(): void
    {
        $admin = $this->admin();

        $this->unguarded()
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/admin/login', [
                'administrator_email' => $admin->email,
                'administrator_password' => 'Original-Passw0rd!',
                'redirect' => 'https://evil.example.com/steal',
            ])
            ->assertHeader('X-Inertia-Location', url('/admin/dashboard'));
    }

    public function test_the_admin_login_keeps_a_same_site_redirect(): void
    {
        $admin = $this->admin();

        $this->unguarded()
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/admin/login', [
                'administrator_email' => $admin->email,
                'administrator_password' => 'Original-Passw0rd!',
                'redirect' => '/admin/general-settings',
            ])
            ->assertHeader('X-Inertia-Location', '/admin/general-settings');
    }

    public function test_a_member_cannot_use_the_admin_login(): void
    {
        $member = $this->member();

        $this->unguarded()
            ->from('/admin/login')
            ->post('/admin/login', [
                'administrator_email' => $member->email,
                'administrator_password' => 'Original-Passw0rd!',
            ])
            ->assertRedirect('/admin/login');

        $this->assertSame('Invalid account type', session('error'));
        $this->assertGuest();
    }

    public function test_the_admin_login_validates_its_fields(): void
    {
        $this->unguarded()
            ->from('/admin/login')
            ->post('/admin/login', ['administrator_email' => 'nope', 'administrator_password' => 'short'])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors(['administrator_email', 'administrator_password']);
    }

    private function pageProps(string $html): array
    {
        $this->assertMatchesRegularExpression('/data-page="([^"]*)"/', $html);

        preg_match('/data-page="([^"]*)"/', $html, $matches);

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true)['props'];
    }
}
