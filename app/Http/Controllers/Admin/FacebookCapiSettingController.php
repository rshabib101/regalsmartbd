<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacebookCapiSetting;
use App\Models\EcomPixel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Toastr;

class FacebookCapiSettingController extends Controller
{
    /**
     * Show unified Facebook Pixel & CAPI configuration screen.
     */
    public function edit()
    {
        $setting = FacebookCapiSetting::first();

        // If no CAPI setting yet, check if an existing EcomPixel exists to pre-populate pixel_id
        if (!$setting) {
            $existingPixel = EcomPixel::first();
            $setting = new FacebookCapiSetting([
                'pixel_id' => $existingPixel ? $existingPixel->code : '',
                'status'   => $existingPixel ? $existingPixel->status : 1,
            ]);
        }

        return view('backEnd.settings.facebook_capi', compact('setting'));
    }

    /**
     * Save / update unified Facebook Pixel & CAPI credentials.
     * Automatically synchronizes with EcomPixel for browser-side tracking.
     */
    public function update(Request $request)
    {
        $request->validate([
            'pixel_id'        => 'required|string|max:255',
            'access_token'    => 'nullable|string',
            'test_event_code' => 'nullable|string|max:255',
            'status'          => 'nullable|boolean',
        ]);

        $status = $request->has('status') ? 1 : 0;

        $data = [
            'pixel_id'        => trim($request->pixel_id),
            'access_token'    => trim($request->access_token ?? ''),
            'test_event_code' => trim($request->test_event_code ?? ''),
            'status'          => $status,
        ];

        // 1. Update / Create FacebookCapiSetting (Server-Side CAPI)
        $setting = FacebookCapiSetting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            FacebookCapiSetting::create($data);
        }

        // 2. Automatically sync with EcomPixel (Browser-Side fbevents.js)
        $pixel = EcomPixel::first();
        if ($pixel) {
            $pixel->update([
                'code'   => trim($request->pixel_id),
                'status' => $status,
            ]);
        } else {
            EcomPixel::create([
                'code'   => trim($request->pixel_id),
                'status' => $status,
            ]);
        }

        // 3. Clear cache so new settings are immediately active everywhere
        Cache::forget('facebook_capi_settings');
        Cache::forget('pixels_list');

        Toastr::success('Facebook Pixel and Conversion API (CAPI) settings updated successfully!', 'Success');

        return redirect()->route('admin.facebook_capi.edit');
    }
}
