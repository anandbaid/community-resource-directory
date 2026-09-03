<?php

namespace Tests\Feature;

use App\Models\EmailTemplates;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Login, register and the password reset pair as Inertia pages.
 *
 * The one thing that must not change here is the WordPress hand-off: a
 * `laravel_wp_auth` cookie is queued on login and dropped on logout, and a
 * WordPress install alongside this app reads it to trust the session.
 */
class AuthVueTest extends TestCase
{
    private const EMAIL = 'auth-smoke-a@example.com';

    protected function tearDown(): void
    {
        DB::table('password_reset_tokens')->where('email', 'like', 'auth-smoke-%@example.com')->delete();
        User::where('email', 'like', 'auth-smoke-%@example.com')->delete();
        EmailTemplates::where('name', 'like', 'Auth Smoke%')->delete();

        parent::tearDown();
    }

    private function member(string $password = 'Original-Passw0rd!'): User
    {
        $user = new User();
        $user->first_name = 'Auth';
        $user->last_name = 'Smoke';
        $user->name = 'Auth Smoke';
        $user->email = self::EMAIL;
        $user->role = 'user';
        $user->status = 'active';
        $user->password = Hash::make($password);
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    /**
     * Registration and the reset link both send templated mail; seed the two
     * templates CommonFunction::sendMail() looks up by id.
     */
    private function seedTemplates(): void
    {
        foreach ([1 => 'Auth Smoke Reset', 2 => 'Auth Smoke Credentials'] as $id => $name) {
            if (EmailTemplates::find($id)) {
                continue;
            }

            $template = new EmailTemplates();
            $template->id = $id;
            $template->name = $name;
            $template->title = $name;
            $template->content = 'Hello #Name#';
            $template->status = 'active';
            $template->save();
        }
    }

    private function unguarded()
    {
        return $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\RecaptchaProtection::class,
        ]);
    }

    public function test_auth_screens_render_their_vue_pages(): void
    {
        $screens = [
            '/login' => 'Auth\\/Login',
            '/register' => 'Auth\\/Register',
            '/password-reset' => 'Auth\\/ForgotPassword',
            '/password-reset/some-token' => 'Auth\\/ResetPassword',
        ];

        foreach ($screens as $url => $component) {
            $response = $this->get($url);

            $response->assertOk();
            $response->assertSee($component, false);
            // These pages sit on the dark banner, not the light account chrome.
            $response->assertDontSee('<section class="light-back">', false);
        }
    }

    public function test_logging_in_hands_off_to_home_and_queues_the_wordpress_cookie(): void
    {
        $user = $this->member();

        $response = $this->unguarded()
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/login', [
                'login_email' => $user->email,
                'login_password' => 'Original-Passw0rd!',
            ]);

        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', url('/home'));
        $response->assertCookie('laravel_wp_auth');
        $this->assertAuthenticatedAs($user);

        // Name, expiry and the shared secret, pipe separated — the format the
        // WordPress side parses.
        $this->assertMatchesRegularExpression(
            '/^Auth Smoke\|\d{10}\|/',
            $response->getCookie('laravel_wp_auth', false)->getValue(),
        );
    }

    public function test_logging_out_forgets_the_wordpress_cookie(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)->get('/logout');

        $response->assertRedirect('/home');
        $this->assertGuest();
        $response->assertCookieExpired('laravel_wp_auth');
    }

    public function test_bad_credentials_come_back_as_a_flashed_error(): void
    {
        $this->member();

        $this->unguarded()
            ->from('/login')
            ->post('/login', ['login_email' => self::EMAIL, 'login_password' => 'wrong'])
            ->assertRedirect('/login');

        $this->assertSame('Invalid username or password!', session('error'));
        $this->assertGuest();
    }

    public function test_login_validates_its_fields(): void
    {
        $this->unguarded()
            ->from('/login')
            ->post('/login', ['login_email' => 'not-an-email', 'login_password' => ''])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['login_email', 'login_password']);
    }

    /** A staff account is refused on the public form, as before. */
    public function test_admins_cannot_use_the_public_login(): void
    {
        $user = $this->member();
        $user->role = 'admin';
        $user->save();

        $this->unguarded()
            ->from('/login')
            ->post('/login', ['login_email' => $user->email, 'login_password' => 'Original-Passw0rd!'])
            ->assertRedirect('/login');

        $this->assertSame('Invalid account type', session('error'));
        $this->assertGuest();
    }

    /** `?redirect=` used to be followed verbatim after authenticating. */
    public function test_login_refuses_an_offsite_redirect(): void
    {
        $user = $this->member();

        $this->unguarded()
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/login', [
                'login_email' => $user->email,
                'login_password' => 'Original-Passw0rd!',
                'redirect' => 'https://evil.example.com/steal',
            ])
            ->assertHeader('X-Inertia-Location', url('/home'));
    }

    public function test_login_keeps_a_same_site_redirect(): void
    {
        $user = $this->member();

        $this->unguarded()
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/login', [
                'login_email' => $user->email,
                'login_password' => 'Original-Passw0rd!',
                'redirect' => '/saved-resources-view',
            ])
            ->assertHeader('X-Inertia-Location', '/saved-resources-view');
    }

    /** Protocol-relative URLs are the classic way past a "starts with /" check. */
    public function test_login_refuses_a_protocol_relative_redirect(): void
    {
        $user = $this->member();

        $this->unguarded()
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/login', [
                'login_email' => $user->email,
                'login_password' => 'Original-Passw0rd!',
                'redirect' => '//evil.example.com/steal',
            ])
            ->assertHeader('X-Inertia-Location', url('/home'));
    }

    /** The WordPress side sends absolute URLs back into this app. */
    public function test_login_keeps_an_absolute_same_host_redirect(): void
    {
        $user = $this->member();

        $this->unguarded()
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/login', [
                'login_email' => $user->email,
                'login_password' => 'Original-Passw0rd!',
                'redirect' => url('/saved-resources-view'),
            ])
            ->assertHeader('X-Inertia-Location', url('/saved-resources-view'));
    }

    public function test_registering_creates_an_active_member_and_signs_them_in(): void
    {
        $this->seedTemplates();

        $response = $this->unguarded()
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/register-submit', [
                'register_name' => 'Auth Smoke',
                'register_phone' => '5551112222',
                'register_email' => self::EMAIL,
                'register_zip' => '35004',
            ]);

        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', url('/home'));
        $response->assertCookie('laravel_wp_auth');

        $user = User::where('email', self::EMAIL)->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertSame('Auth Smoke', $user->name);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registering_returns_field_errors(): void
    {
        $this->member();

        $this->unguarded()
            ->from('/register')
            ->post('/register-submit', [
                'register_name' => '',
                'register_phone' => 'abc',
                'register_email' => self::EMAIL,
                'register_zip' => 'nope',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['register_name', 'register_phone', 'register_email', 'register_zip']);
    }

    /**
     * The form used to run `exists:users,email`, so an unknown address produced
     * a validation error and a known one did not — an account oracle.
     */
    public function test_the_reset_form_does_not_reveal_whether_an_account_exists(): void
    {
        $this->seedTemplates();
        $this->member();

        foreach ([self::EMAIL, 'auth-smoke-nobody@example.com'] as $email) {
            $response = $this->unguarded()
                ->withHeaders(['X-Inertia' => 'true'])
                ->post('/password-reset', ['user_email' => $email]);

            $response->assertStatus(409);
            $response->assertHeader('X-Inertia-Location', url('/login'));

            $this->flushHeaders();
        }

        // Only the real account got a token.
        $this->assertSame(1, DB::table('password_reset_tokens')->where('email', 'like', 'auth-smoke-%')->count());
    }

    public function test_a_valid_token_updates_the_password(): void
    {
        $user = $this->member();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => 'valid-token', 'created_at' => now()],
        );

        $response = $this->unguarded()
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/password-reset/update', [
                'token' => 'valid-token',
                'user_email' => $user->email,
                'password' => 'Brand-New-Passw0rd!',
                'password_confirmation' => 'Brand-New-Passw0rd!',
            ]);

        $response->assertHeader('X-Inertia-Location', url('/login'));

        $user->refresh();
        $this->assertTrue(Hash::check('Brand-New-Passw0rd!', $user->password));
        $this->assertSame(0, DB::table('password_reset_tokens')->where('email', $user->email)->count());
    }

    /** Nothing used to read `created_at`, so a leaked link never went stale. */
    public function test_an_expired_token_is_refused_and_discarded(): void
    {
        $user = $this->member();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => 'stale-token', 'created_at' => now()->subHours(3)],
        );

        $this->unguarded()
            ->from('/password-reset/stale-token')
            ->post('/password-reset/update', [
                'token' => 'stale-token',
                'user_email' => $user->email,
                'password' => 'Brand-New-Passw0rd!',
                'password_confirmation' => 'Brand-New-Passw0rd!',
            ])
            ->assertSessionHasErrors('token');

        $user->refresh();
        $this->assertTrue(Hash::check('Original-Passw0rd!', $user->password));
        $this->assertSame(0, DB::table('password_reset_tokens')->where('email', $user->email)->count());
    }

    public function test_a_wrong_token_is_refused(): void
    {
        $user = $this->member();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => 'valid-token', 'created_at' => now()],
        );

        $this->unguarded()
            ->from('/password-reset/other-token')
            ->post('/password-reset/update', [
                'token' => 'other-token',
                'user_email' => $user->email,
                'password' => 'Brand-New-Passw0rd!',
                'password_confirmation' => 'Brand-New-Passw0rd!',
            ])
            ->assertSessionHasErrors('token');

        $user->refresh();
        $this->assertTrue(Hash::check('Original-Passw0rd!', $user->password));
    }

    public function test_password_update_enforces_confirmation_and_length(): void
    {
        $user = $this->member();

        $this->unguarded()
            ->from('/password-reset/valid-token')
            ->post('/password-reset/update', [
                'token' => 'valid-token',
                'user_email' => $user->email,
                'password' => 'short',
                'password_confirmation' => 'mismatch',
            ])
            ->assertSessionHasErrors('password');
    }
}
