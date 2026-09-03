<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\RedirectsSafely;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminLoginController extends Controller
{
    use RedirectsSafely;

    public function loginView(Request $request)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        return Inertia::render('Auth/AdminLogin', [
            'submitUrl' => route('admin.login'),
            'redirect' => $this->safeRedirect($request->query('redirect')),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'administrator_email' => 'required|email',
            'administrator_password' => 'required|min:8',
        ]);

        $credentials = [
            'email' => $request->get('administrator_email'),
            'password' => $request->get('administrator_password'),
        ];

        if (!Auth::attempt($credentials)) {
            return back()->with('error', 'Invalid username or password!');
        }

        if (Auth::user()->role !== 'admin') {
            Auth::logout();

            return back()->with('error', 'Invalid account type');
        }

        // The posted `redirect` used to be followed as-is, on the endpoint that
        // hands out an administrator session.
        return Inertia::location(
            $this->safeRedirect($request->input('redirect')) ?: url('/admin/dashboard'),
        );
    }

    public function logout()
    {
        Auth::logout();

        return redirect('admin/login');
    }
}
