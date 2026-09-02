<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FacebookServerPageView
{
    /**
     * Routes/path prefixes যেগুলোতে PageView fire হবে না।
     * Admin, API, AJAX, asset, webhook, sitemap ইত্যাদি বাদ।
     */
    protected array $skipPrefixes = [
        'admin',
        'api/',
        'vendor/',
        'reseller/',
        'customer/order-save',
        'customer/signin',
        'customer/store',
        'customer/verify',
        'bkash/',
        'uddoktapay/',
        'aamarpay/',
        'payment-success',
        'payment-cancel',
        'incomplete-order',
        'redx/',
        'style.css',
        'responsive.css',
        'dynamic-theme.css',
        'facebook-catalog',
        'sitemap',
        'digital-download',
        'cart/count',
        'mobilecart/count',
        'cart/sidebar',
        'cart/decrement',
        'cart/increment',
        'cart/update',
        'cart/change-product',
        'livesearch',
        'shipping-charge',
        'ajax-product',
        'ajax-wholesale',
        'add-to-cart',
        'quick-view',
        'districts',
        'cc',
        'up',
    ];

    /**
     * handle(): unique pv_event_id generate করে request ও view এ share করে।
     * Browser pixel এ এই same ID ব্যবহার হবে → deduplication কাজ করবে।
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldTrack($request)) {
            $pvEventId = 'pv_' . substr(md5(
                $request->fullUrl() . '|' . $request->ip() . '|' . $request->userAgent() . '|' . microtime()
            ), 0, 20);

            // Request attribute এ রাখো (terminate() এ দরকার হবে)
            $request->attributes->set('fb_pv_event_id', $pvEventId);

            // Blade view এ share করো — master.blade.php এ $fb_pv_event_id পাওয়া যাবে
            view()->share('fb_pv_event_id', $pvEventId);
        }

        return $next($request);
    }

    /**
     * terminate(): Response পাঠানোর পরে CAPI PageView fire করে (non-blocking)।
     */
    public function terminate(Request $request, Response $response): void
    {
        if (!$this->shouldTrack($request)) {
            return;
        }

        $pvEventId = $request->attributes->get('fb_pv_event_id');
        if (!$pvEventId) {
            return;
        }

        // Closure এ pass করার জন্য scalar variables
        $ip        = $request->ip();
        $ua        = $request->userAgent();
        $fbp       = $request->hasCookie('_fbp') ? $request->cookie('_fbp') : null;
        $fbc       = $request->hasCookie('_fbc') ? $request->cookie('_fbc') : null;
        $fullUrl   = $request->fullUrl();
        $referer   = $request->headers->get('referer', '');
        $routeName = $request->route()?->getName() ?? '';

        // Authenticated customer data
        $custPhone = null;
        $custEmail = null;
        $custId    = null;
        if (auth('customer')->check()) {
            $customer  = auth('customer')->user();
            $custPhone = $customer->phone ?? null;
            $custEmail = $customer->email ?? null;
            $custId    = $customer->id    ?? null;
        }

        dispatch(function () use (
            $pvEventId, $ip, $ua, $fbp, $fbc,
            $fullUrl, $referer, $routeName,
            $custPhone, $custEmail, $custId
        ) {
            try {
                $capiService = app(\App\Services\FacebookCapiService::class);

                $userData = [
                    'client_ip_address' => $ip,
                    'client_user_agent' => $ua,
                ];
                if ($fbp)       $userData['fbp']         = $fbp;
                if ($fbc)       $userData['fbc']         = $fbc;
                if ($custPhone) $userData['phone']       = $custPhone;
                if ($custEmail) $userData['email']       = $custEmail;
                if ($custId)    $userData['external_id'] = (string) $custId;

                $pageTitle = $this->getPageTitleFromRoute($routeName);

                $customData = [
                    'page_title' => $pageTitle,
                    'referrer'   => $referer,
                    'page_url'   => $fullUrl,
                ];

                $capiService->sendPageView($customData, $userData, [
                    'event_source_url' => $fullUrl,
                    'event_id'         => $pvEventId, // ⭐ Browser pixel এর same ID → deduplication
                ]);
            } catch (\Throwable $e) {
                // non-blocking — silently fail
            }
        })->afterResponse();
    }

    /**
     * Track করা উচিত কিনা determine করে।
     */
    protected function shouldTrack(Request $request): bool
    {
        // শুধু GET request
        if (!$request->isMethod('GET')) {
            return false;
        }
        // AJAX/JSON request বাদ
        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }
        // Skip prefixes চেক
        $path = ltrim($request->path(), '/');
        foreach ($this->skipPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Route name থেকে human-readable page title।
     */
    protected function getPageTitleFromRoute(string $routeName): string
    {
        $titles = [
            'home'                   => 'Home',
            'product'                => 'Product Details',
            'products'               => 'Products',
            'category'               => 'Category',
            'subcategory'            => 'Subcategory',
            'shop'                   => 'Shop',
            'search'                 => 'Search Results',
            'hotdeals'               => 'Hot Deals',
            'flashsales'             => 'Flash Sales',
            'cart.show'              => 'Shopping Cart',
            'customer.checkout'      => 'Checkout',
            'contact'                => 'Contact Us',
            'customer.order_success' => 'Order Success',
            'blogs'                  => 'Blog',
            'blog.details'           => 'Blog Post',
            'sellers'                => 'Sellers',
            'campaign'               => 'Campaign',
            'offers'                 => 'Offers',
            'brand.products'         => 'Brand Products',
            'vendor.shop'            => 'Vendor Shop',
        ];
        return $titles[$routeName] ?? config('app.name', 'Smart Grammo Bazar');
    }
}
