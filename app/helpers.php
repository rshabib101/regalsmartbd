<?php

use App\Models\ResellerLandingPage;

if (!function_exists('landing_url')) {
    /**
     * Generate URL for reseller landing - uses custom domain when applicable.
     */
    function landing_url(string $slug, string $path = ''): string
    {
        try {
            $landing = ResellerLandingPage::where('slug', $slug)->first();
            $host = strtolower(request()->getHost());

            if ($landing && $landing->custom_domain && strtolower($landing->custom_domain) === $host) {
                return $path === '' ? url('/') : url($path);
            }
        } catch (\Throwable $e) {
            // Table doesn't exist yet or DB error, fallback
        }

        $base = '/r/' . $slug;
        return $path === '' ? url($base) : url(rtrim($base . '/' . ltrim($path, '/'), '/'));
    }
}
