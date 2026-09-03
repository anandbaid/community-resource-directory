<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class RecaptchaProtection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->input('recaptcha_token');

        if (!$token) {
            return $this->reject($request, 'Recaptcha token missing.');
        }

        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => $request->ip(),
            ]
        );

        $result = $response->json();

        // A network failure or a malformed reply leaves $result null, which used
        // to raise a 500 out of the array access below.
        if (
            !is_array($result) ||
            !($result['success'] ?? false) ||
            ($result['score'] ?? 0) < 0.5
        ) {
            return $this->reject($request, 'Recaptcha verification failed.');
        }

        return $next($request);
    }

    /**
     * Inertia submissions and plain form posts want a redirect carrying the
     * error bag; the Vue islands call this over XHR and need readable JSON.
     */
    private function reject(Request $request, string $message): Response
    {
        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'status' => 'error',
                'errors' => $message,
            ], 422);
        }

        return back()->withErrors(['captcha' => $message]);
    }
}
