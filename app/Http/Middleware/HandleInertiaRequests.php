<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on the first page visit.
     *
     * Admin pages keep the existing AdminLTE chrome (head/header/sidebar/foot)
     * so Blade and Inertia screens can coexist while the app is migrated.
     */
    protected $rootView = 'backend.layouts.inertia';

    /**
     * Admin and public pages have completely different chrome, so pick the root
     * template per request rather than pinning one.
     */
    public function rootView(Request $request): string
    {
        if (!$request->is('admin', 'admin/*')) {
            return 'frontend.layouts.inertia';
        }

        // The admin header and sidebar are built from the signed-in user, so
        // the login screen gets a chrome-less shell.
        return $request->is('admin/login')
            ? 'backend.layouts.inertia-blank'
            : 'backend.layouts.inertia';
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ] : null,
            ],
            // Mirrors resources/views/common/flash.blade.php so converted pages
            // can raise the same SweetAlert toasts the Blade pages do.
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ]);
    }
}
