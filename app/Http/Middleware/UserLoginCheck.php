<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserLoginCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = auth()->user();
            if ($user->status != 'active') {
                auth()->logout();
                return redirect('login?redirect=' . $request->path())->with('error', "Your account has been blocked please contact support.");
            } elseif ($user->role != 'user') {
                auth()->logout();
                return redirect('login?redirect=' . $request->path())->with('error', "Please login with Your credentials.");
            } else {
                return $next($request);
            }
        } else {
            return redirect('login?redirect=' . $request->path())->with('error', "Please login with Your credentials.");
        }
    }
}
