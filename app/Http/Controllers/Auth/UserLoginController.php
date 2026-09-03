<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\RedirectsSafely;
use App\Http\Controllers\CommonFunction;
use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class UserLoginController extends Controller
{
    use RedirectsSafely;

    /**
     * How long a password reset token stays usable.
     *
     * The table has always stored `created_at`; nothing used to read it, so a
     * link that leaked out of an inbox stayed valid forever.
     */
    private const RESET_TOKEN_MINUTES = 60;

    public function register()
    {
        if (Auth::check() && auth()->user()->role == 'user') {
            return redirect('/home');
        }

        return Inertia::render('Auth/Register', [
            'submitUrl' => route('register-submit'),
            'loginUrl' => url('login'),
            'bannerImage' => asset('assets/img/banner.png'),
            'lightBack' => false,
        ]);
    }

    public function registerUsers(Request $request)
    {
        $request->validate([
            'register_name' => 'required|string',
            'register_phone' => 'required|regex:/^[0-9]{10,20}$/',
            'register_email' => 'required|email|unique:users,email',
            'register_zip' => 'required|regex:/^\d{5}(-\d{4})?$/',
        ], [
            'register_name.required' => 'Please enter your name.',
            'register_phone.regex' => 'Please enter a valid phone number.',
            'register_email.unique' => 'An account already exists for this email address.',
            'register_zip.regex' => 'Please enter a valid zip code.',
        ]);

        try {
            DB::beginTransaction();

            $random_password = Str::random(8);
            $name = trim($request->register_name);

            $user = new User();
            if (Str::contains($name, ' ')) {
                $user->first_name = Str::before($name, ' ');
                $user->last_name = Str::after($name, ' ');
            } else {
                $user->first_name = $name;
                $user->last_name = ''; // Set last name as empty if no space is found
            }
            $user->name = trim($user->first_name . ' ' . $user->last_name);
            $user->email = $request->register_email;
            $user->password = Hash::make($random_password);
            $user->role = 'user';
            $user->status = 'active';
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->phone = $request->register_phone;
            $user->zipcode = $request->register_zip;
            $user->save();

            $this->mailCredentials($user, $random_password);

            if (Auth::attempt(['email' => $user->email, 'password' => $random_password])) {
                $this->setWordPressAuthCookies(auth()->user()->name);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        session()->flash('success', 'User successfully registered!!');

        // /home is still a Blade page, so hand the browser over rather than
        // asking Inertia to resolve a component for it.
        return Inertia::location(route('home'));
    }

    public function loginView(Request $request)
    {
        if (Auth::check() && auth()->user()->role == 'user') {
            return redirect('/home');
        }

        return Inertia::render('Auth/Login', [
            'submitUrl' => route('login'),
            'registerUrl' => url('register'),
            'forgotUrl' => url('password-reset'),
            'bannerImage' => asset('assets/img/banner.png'),
            'redirect' => $this->safeRedirect($request->get('redirect')),
            'lightBack' => false,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_email' => 'required|email',
            'login_password' => 'required',
        ]);

        $credentials = [
            'email' => $request->get('login_email'),
            'password' => $request->get('login_password'),
        ];

        if (!Auth::attempt($credentials)) {
            return back()->with('error', 'Invalid username or password!');
        }

        $user = auth()->user();

        if ($user->role != 'user') {
            Auth::logout();

            return back()->with('error', 'Invalid account type');
        }

        $this->setWordPressAuthCookies($user->name);

        // The posted `redirect` used to be followed as-is, so a crafted
        // /login?redirect=https://… link bounced people off-site with a
        // freshly authenticated session.
        return Inertia::location($this->safeRedirect($request->input('redirect')) ?: url('/home'));
    }

    public function logout()
    {
        Auth::logout();
        Cookie::queue(Cookie::forget('laravel_wp_auth'));

        return redirect('/home');
    }

    public function setWordPressAuthCookies($user_name)
    {
        $expiration = time() + (2 * 60 * 60); // 2 hours expiration
        $secret_key = config('services.wordpress.token');
        // Store user_id and token in a cookie
        Cookie::queue('laravel_wp_auth', "$user_name|$expiration|$secret_key", 120);
    }

    public function showResetForm()
    {
        return Inertia::render('Auth/ForgotPassword', [
            'submitUrl' => route('password.email'),
            'bannerImage' => asset('assets/img/banner.png'),
            'lightBack' => false,
        ]);
    }

    // Handle password reset link request
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'user_email' => 'required|email',
        ]);

        $user = User::where('email', $request->user_email)->where('status', 'active')->first();

        // Answer the same way whether or not the address is on file: the old
        // `exists:users,email` rule turned this form into an account oracle,
        // and a non-active match walked into a null dereference below.
        if ($user) {
            try {
                $token = Str::random(60);

                DB::table('password_reset_tokens')->updateOrInsert(
                    ['email' => $user->email],
                    ['token' => $token, 'created_at' => now()]
                );

                $this->mailResetLink($user, $token);
            } catch (\Exception $e) {
                return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
            }
        }

        session()->flash('success', 'If that address has an account, a password reset link is on its way.');

        return Inertia::location(route('login'));
    }

    // Show new password form
    public function showNewPasswordForm($token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'submitUrl' => route('password.update'),
            'bannerImage' => asset('assets/img/banner.png'),
            'token' => $token,
            'lightBack' => false,
        ]);
    }

    // Handle password update
    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'user_email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $reset = DB::table('password_reset_tokens')->where([
            'email' => $request->user_email,
            'token' => $request->token,
        ])->first();

        if (!$reset) {
            return back()->withErrors(['token' => 'This password reset link is not valid.']);
        }

        if (Carbon::parse($reset->created_at)->addMinutes(self::RESET_TOKEN_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->user_email)->delete();

            return back()->withErrors(['token' => 'This password reset link has expired. Please request a new one.']);
        }

        try {
            User::where('email', $request->user_email)->update([
                'password' => Hash::make($request->password),
            ]);

            DB::table('password_reset_tokens')->where('email', $request->user_email)->delete();
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        session()->flash('success', 'Password updated successfully.');

        return Inertia::location(route('login'));
    }

    private function mailCredentials(User $user, string $password): void
    {
        $check_array = ['#Name#', '#UserName#', '#Password#', '#SiteURL#', '#SiteLogo#', '#FooterCopyright#'];
        $replace_array = [$user->name, $user->email, $password, url('/'), $this->logoUrl(), $this->copyright()];

        CommonFunction::sendMail(2, $user->email, $check_array, $replace_array);
    }

    private function mailResetLink(User $user, string $token): void
    {
        $check_array = ['#Name#', '#UserEmail#', '#PasswordResetLink#', '#SiteURL#', '#SiteLogo#', '#FooterCopyright#'];
        $replace_array = [
            $user->name,
            $user->email,
            url('/password-reset/' . $token),
            url('/'),
            $this->logoUrl(),
            $this->copyright(),
        ];

        CommonFunction::sendMail(1, $user->email, $check_array, $replace_array);
    }

    private function logoUrl(): string
    {
        return url(SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png');
    }

    private function copyright(): string
    {
        return SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';
    }
}
