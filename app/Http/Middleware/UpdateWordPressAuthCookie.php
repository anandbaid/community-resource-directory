<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class UpdateWordPressAuthCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role == 'user') {
            $user_name = Auth::user()->name; // Get username
            $expiration = time() + (2 * 60 * 60); // 2 hours expiration
            $secret_key = config('services.wordpress.token');
            // Update cookie with new expiration time
            Cookie::queue('laravel_wp_auth', "$user_name|$expiration|$secret_key", 120); // 120 minutes = 2 hours
        } else {
            Cookie::queue(Cookie::forget('laravel_wp_auth'));
        }

        return $next($request);
    }
}
