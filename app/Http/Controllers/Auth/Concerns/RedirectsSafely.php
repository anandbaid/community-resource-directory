<?php

namespace App\Http\Controllers\Auth\Concerns;

trait RedirectsSafely
{
    /**
     * Reduce a requested post-login destination to a same-site one.
     *
     * Both login forms carried the requested destination in a field and then
     * followed it verbatim, so a crafted link could bounce someone off-site
     * with a session that had just been authenticated.
     *
     * Relative paths pass through; an absolute URL survives only when its host
     * matches this app's. A protocol-relative `//evil.example.com` reads as a
     * path to a naive "starts with /" check, so the value is parsed rather than
     * pattern-matched.
     */
    protected function safeRedirect(?string $target): string
    {
        $target = trim((string) $target);

        if ($target === '') {
            return '';
        }

        $host = parse_url($target, PHP_URL_HOST);

        if ($host === null && str_starts_with($target, '/')) {
            return $target;
        }

        return $host !== null && $host === parse_url(url('/'), PHP_URL_HOST) ? $target : '';
    }
}
