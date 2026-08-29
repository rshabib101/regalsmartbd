<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TiktokPixel;
use Illuminate\Support\Facades\Cache;
use Toastr;

class TiktokPixelsController extends Controller
{
    /**
     * Unified TikTok Pixel and Events API configuration screen.
     */
    public function index()
    {
        $pixel = TiktokPixel::first();
        if (!$pixel) {
            $pixel = new TiktokPixel([
                'status' => 1,
            ]);
        }

        return view('backEnd.tiktok_pixels.index', compact('pixel'));
    }

    public function create()
    {
        return redirect()->route('tiktok.pixels.index');
    }

    public function store(Request $request)
    {
        return $this->update($request);
    }

    public function edit($id)
    {
        return redirect()->route('tiktok.pixels.index');
    }

    /**
     * Save / update unified TikTok Pixel & Events API credentials.
     */
    public function update(Request $request)
    {
        $request->validate([
            'code'            => 'required|string|max:255',
            'access_token'    => 'nullable|string',
            'test_event_code' => 'nullable|string|max:255',
            'status'          => 'nullable|boolean',
        ]);

        $status = $request->has('status') ? 1 : 0;

        $data = [
            'code'            => trim($request->code),
            'access_token'    => trim($request->access_token ?? ''),
            'test_event_code' => trim($request->test_event_code ?? ''),
            'status'          => $status,
        ];

        $pixel = TiktokPixel::first();
        if ($pixel) {
            $pixel->update($data);
        } else {
            TiktokPixel::create($data);
        }

        Cache::forget('tiktok_pixels_list');
        Cache::forget('tiktok_events_api_settings');

        Toastr::success('TikTok Pixel and Events API settings updated successfully!', 'Success');

        return redirect()->route('tiktok.pixels.index');
    }

    public function inactive(Request $request)
    {
        $pixel = TiktokPixel::first();
        if ($pixel) {
            $pixel->status = 0;
            $pixel->save();
            Cache::forget('tiktok_pixels_list');
            Cache::forget('tiktok_events_api_settings');
        }
        Toastr::success('TikTok tracking deactivated.', 'Success');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $pixel = TiktokPixel::first();
        if ($pixel) {
            $pixel->status = 1;
            $pixel->save();
            Cache::forget('tiktok_pixels_list');
            Cache::forget('tiktok_events_api_settings');
        }
        Toastr::success('TikTok tracking activated.', 'Success');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $pixel = TiktokPixel::find($request->hidden_id ?? $request->id);
        if ($pixel) {
            $pixel->delete();
            Cache::forget('tiktok_pixels_list');
            Cache::forget('tiktok_events_api_settings');
        }
        Toastr::success('TikTok Pixel deleted.', 'Success');
        return redirect()->route('tiktok.pixels.index');
    }
}
