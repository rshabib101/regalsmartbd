@extends('backEnd.layouts.master')
@section('title','TikTok Pixel & Events API Setup')

@section('css')
<style>
    .tracking-card {
        border: none;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }
    .tracking-card-header {
        background: #fff;
        border-bottom: 1px solid #edf2f7;
        padding: 22px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .tracking-card-title {
        font-size: 17px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .platform-icon-tt {
        width: 40px;
        height: 40px;
        background: #000000;
        color: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }
    .status-badge-box {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-pill-success {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .status-pill-muted {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .status-dot-active {
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    }
    .status-dot-inactive {
        background: #94a3b8;
    }
    .form-label {
        font-weight: 600;
        font-size: 13px;
        color: #334155;
        margin-bottom: 7px;
    }
    .form-control {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 11px 15px;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        transition: all 0.2s ease;
    }
    .form-control:focus {
        background-color: #fff;
        border-color: #000;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.15);
    }
    .small-help {
        font-size: 12px;
        color: #64748b;
        margin-top: 5px;
        display: block;
    }
    .btn-submit-tt {
        background: linear-gradient(135deg, #000000, #1e293b);
        border: none;
        color: white;
        padding: 12px 28px;
        font-weight: 600;
        font-size: 14px;
        border-radius: 40px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
        transition: all 0.25s ease;
    }
    .btn-submit-tt:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
        color: #fff;
    }
    .info-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
    }
    .info-card h6 {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 12px;
        font-size: 14px;
    }
    .info-step {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
        font-size: 13px;
        color: #475569;
    }
    .info-step-num {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #000;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
    }
    /* Toggle Switch */
    .switch { position: relative; display: inline-block; width: 52px; height: 28px; margin: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 22px; width: 22px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    input:checked + .slider { background-color: #10b981; }
    input:checked + .slider:before { transform: translateX(24px); }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="page-title mb-0" style="font-weight: 700; color: #1e293b;">
                    TikTok Pixel & Events API
                </h4>
                <p class="text-muted font-size-13 mb-0">
                    ব্রাউজার সাইড (TikTok Pixel) এবং সার্ভার সাইড (Events API) ট্র্যাকিং একই সাথে কনফিগার করুন।
                </p>
            </div>
            
            {{-- Status Indicators --}}
            <div class="status-badge-box">
                @if($pixel->status && !empty($pixel->code))
                    <span class="status-pill status-pill-success">
                        <span class="status-dot status-dot-active"></span> Browser Pixel: Active
                    </span>
                    @if(!empty($pixel->access_token))
                    <span class="status-pill status-pill-success">
                        <span class="status-dot status-dot-active"></span> Server Events API: Active
                    </span>
                    @else
                    <span class="status-pill status-pill-muted">
                        <span class="status-dot status-dot-inactive"></span> Server API: Token Needed
                    </span>
                    @endif
                @else
                    <span class="status-pill status-pill-muted">
                        <span class="status-dot status-dot-inactive"></span> Tracking: Inactive
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Settings Form --}}
        <div class="col-lg-7 col-xl-8">
            <div class="tracking-card">
                <div class="tracking-card-header">
                    <h5 class="tracking-card-title">
                        <span class="platform-icon-tt">
                            <i class="fab fa-tiktok"></i>
                        </span>
                        TikTok Credentials Configuration
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('tiktok.pixels.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="hidden_id" value="{{ $pixel->id ?? '' }}">

                        {{-- Pixel ID --}}
                        <div class="mb-4">
                            <label class="form-label" for="code">
                                TikTok Pixel ID <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('code') is-invalid @enderror"
                                id="code"
                                name="code"
                                value="{{ old('code', $pixel->code ?? '') }}"
                                placeholder="যেমন: C92H38BC77U54L69K0CG"
                                required
                            >
                            <small class="small-help">
                                <i class="fas fa-info-circle me-1 text-dark"></i>
                                TikTok Ads Manager &gt; Assets &gt; Events &gt; Web Events থেকে Pixel ID কপি করুন।
                            </small>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Access Token --}}
                        <div class="mb-4">
                            <label class="form-label" for="access_token">
                                TikTok Events API Access Token
                            </label>
                            <textarea
                                class="form-control @error('access_token') is-invalid @enderror"
                                id="access_token"
                                name="access_token"
                                rows="3"
                                placeholder="TikTok Events Manager থেকে জেনারেট করা Access Token এখানে দিন..."
                            >{{ old('access_token', $pixel->access_token ?? '') }}</textarea>
                            <small class="small-help">
                                সার্ভার সাইড (Events API) ট্র্যাকিংয়ের জন্য আবশ্যক। Web Events &gt; Settings &gt; Events API &gt; Generate Access Token থেকে পাবেন।
                            </small>
                            @error('access_token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Test Event Code --}}
                        <div class="mb-4">
                            <label class="form-label" for="test_event_code">
                                Test Event Code <span class="text-muted fw-normal">(ঐচ্ছিক - টেস্ট করার সময়)</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('test_event_code') is-invalid @enderror"
                                id="test_event_code"
                                name="test_event_code"
                                value="{{ old('test_event_code', $pixel->test_event_code ?? '') }}"
                                placeholder="যেমন: TEST35129"
                            >
                            <small class="small-help">
                                TikTok Events Manager &gt; Test Events ট্যাবে থাকা কোডটি দিন। লাইভ হওয়ার পর এটি খালি রাখবেন।
                            </small>
                            @error('test_event_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status Switch --}}
                        <div class="mb-4 p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">TikTok Tracking Status</h6>
                                <small class="text-muted">সুইচটি চালু রাখলে ব্রাউজার পিক্সেল এবং সার্ভার Events API দুটোই সক্রিয় থাকবে।</small>
                            </div>
                            <label class="switch">
                                <input
                                    type="checkbox"
                                    id="status"
                                    name="status"
                                    value="1"
                                    {{ old('status', $pixel->status ?? 1) ? 'checked' : '' }}
                                >
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-submit-tt">
                                <i class="fas fa-check-circle me-1"></i> Save TikTok Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Guide / Instructions Card --}}
        <div class="col-lg-5 col-xl-4 mt-3 mt-lg-0">
            <div class="info-card">
                <h6><i class="fab fa-tiktok text-dark me-2"></i>কীভাবে সেটআপ করবেন?</h6>

                <div class="info-step">
                    <div class="info-step-num">১</div>
                    <div><strong>TikTok Ads Manager-এ যান:</strong> আপনার TikTok Ads অ্যাকাউন্টে গিয়ে Assets &gt; Events মেনু ওপেন করুন।</div>
                </div>

                <div class="info-step">
                    <div class="info-step-num">২</div>
                    <div><strong>Pixel ID কপি করুন:</strong> Web Events থেকে তৈরি করা Pixel-এর Settings ট্যাবে থাকা Pixel ID টি কপি করুন।</div>
                </div>

                <div class="info-step">
                    <div class="info-step-num">৩</div>
                    <div><strong>Events API Token নিন:</strong> Settings ট্যাবের Events API সেকশনে গিয়ে "Generate Access Token" এ ক্লিক করে টোকেন কপি করুন।</div>
                </div>

                <div class="info-step">
                    <div class="info-step-num">৪</div>
                    <div><strong>সেভ করুন:</strong> সেটিংস সেভ করলেই ব্রাউজার পিক্সেল এবং সার্ভার সাইড (Events API) স্বয়ংক্রিয়ভাবে সক্রিয় হয়ে যাবে।</div>
                </div>

                <hr class="my-3 text-muted">

                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-bolt text-warning fs-5"></i>
                    <small class="text-muted">
                        iOS 14+ এবং Ad-Blocker ব্যবহারকারীদের নির্ভুল পারচেজ ডাটা পেতে Events API কার্যকর ভূমিকা রাখে।
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
