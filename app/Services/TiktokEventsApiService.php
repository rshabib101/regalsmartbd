<?php

namespace App\Services;

use App\Models\TiktokPixel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TiktokEventsApiService
{
    protected $pixelCode;
    protected $accessToken;
    protected $testEventCode;
    protected $initialized = false;

    /**
     * Lazy load credentials
     */
    protected function initialize()
    {
        if ($this->initialized) {
            return;
        }

        try {
            $setting = Cache::remember('tiktok_events_api_settings', 3600, function () {
                return TiktokPixel::where('status', 1)->whereNotNull('access_token')->first() 
                    ?: TiktokPixel::where('status', 1)->first();
            });

            if ($setting) {
                $this->pixelCode = $setting->code;
                $this->accessToken = $setting->access_token;
                $this->testEventCode = $setting->test_event_code;
            }
        } catch (\Throwable $e) {
            Log::warning('TiktokEventsApiService: Failed to load settings - ' . $e->getMessage());
        }

        $this->initialized = true;
    }

    /**
     * Check if TikTok Events API is configured with token
     */
    public function isConfigured(): bool
    {
        $this->initialize();
        return !empty($this->pixelCode) && !empty($this->accessToken);
    }

    /**
     * Send event to TikTok Events API
     *
     * @param string $eventName (e.g. CompletePayment, InitiateCheckout, AddToCart, ViewContent)
     * @param array $data Event data (currency, value, contents, etc.)
     * @param array $userData User info (email, phone, ip, user_agent, ttclid)
     * @param array $options Additional options (event_id, event_time, event_source_url)
     * @return array|false
     */
    public function sendEvent(string $eventName, array $data = [], array $userData = [], array $options = [])
    {
        $this->initialize();

        if (!$this->isConfigured()) {
            return false;
        }

        try {
            // Map standard events if needed
            $tiktokEventMap = [
                'Purchase'         => 'CompletePayment',
                'InitiateCheckout' => 'InitiateCheckout',
                'AddToCart'        => 'AddToCart',
                'ViewContent'      => 'ViewContent',
            ];
            $finalEventName = $tiktokEventMap[$eventName] ?? $eventName;

            // Prepare User Data (Hash PII)
            $userPayload = [
                'ip'         => $userData['ip'] ?? request()->ip(),
                'user_agent' => $userData['user_agent'] ?? request()->userAgent(),
            ];

            if (!empty($userData['email'])) {
                $userPayload['email'] = hash('sha256', strtolower(trim($userData['email'])));
            }

            if (!empty($userData['phone'])) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $userData['phone']);
                if (!str_starts_with($cleanPhone, '880') && str_starts_with($cleanPhone, '01')) {
                    $cleanPhone = '88' . $cleanPhone;
                }
                $userPayload['phone'] = hash('sha256', $cleanPhone);
            }

            // ttclid from cookie or request
            $ttclid = $userData['ttclid'] ?? request()->cookie('ttclid') ?? request()->query('ttclid');
            if ($ttclid) {
                $userPayload['ttclid'] = $ttclid;
            }

            // Prepare properties
            $properties = [];
            if (isset($data['currency'])) {
                $properties['currency'] = $data['currency'];
            }
            if (isset($data['value'])) {
                $properties['value'] = (float)$data['value'];
            }
            if (isset($data['contents'])) {
                $properties['contents'] = array_map(function ($item) {
                    return [
                        'content_id'   => (string)($item['id'] ?? $item['content_id'] ?? ''),
                        'content_type' => 'product',
                        'content_name' => (string)($item['name'] ?? ''),
                        'quantity'     => (int)($item['quantity'] ?? 1),
                        'price'        => (float)($item['item_price'] ?? $item['price'] ?? 0),
                    ];
                }, $data['contents']);
            }

            $eventPayload = [
                'event'      => $finalEventName,
                'event_time' => $options['event_time'] ?? time(),
                'event_id'   => (string)($options['event_id'] ?? ('tt_' . time() . '_' . uniqid())),
                'user'       => $userPayload,
                'properties' => $properties,
            ];

            if (!empty($options['event_source_url'])) {
                $eventPayload['page'] = ['url' => $options['event_source_url']];
            }

            $body = [
                'event_source'    => 'web',
                'event_source_id' => $this->pixelCode,
                'data'            => [$eventPayload],
            ];

            if (!empty($this->testEventCode)) {
                $body['test_event_code'] = $this->testEventCode;
            }

            $response = Http::withHeaders([
                'Access-Token' => $this->accessToken,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post('https://business-api.tiktok.com/open_api/v1.3/event/track/', $body);

            $result = $response->json();

            if ($response->successful() && isset($result['code']) && $result['code'] === 0) {
                Log::info("TikTok Events API: '{$finalEventName}' sent successfully.", ['event_id' => $eventPayload['event_id']]);
                return $result;
            }

            Log::warning("TikTok Events API Warning for '{$finalEventName}'", [
                'status'   => $response->status(),
                'response' => $result,
            ]);
            return $result;

        } catch (\Throwable $e) {
            Log::error('TikTok Events API Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper for Purchase / CompletePayment event
     */
    public function sendPurchase(array $data, array $userData, array $options = [])
    {
        return $this->sendEvent('CompletePayment', $data, $userData, $options);
    }

    /**
     * Helper for InitiateCheckout event
     */
    public function sendInitiateCheckout(array $data, array $userData, array $options = [])
    {
        return $this->sendEvent('InitiateCheckout', $data, $userData, $options);
    }
}
