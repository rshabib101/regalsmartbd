@extends('backEnd.layouts.master')
@section('title','Facebook Pixel & Conversion API (CAPI) Setup')

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
    .platform-icon-fb {
        width: 40px;
        height: 40px;
        background: #1877f2;
        color: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3);
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
        border-color: #1877f2;
        box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.15);
    }
    .small-help {
        font-size: 12px;
        color: #64748b;
        margin-top: 5px;
        display: block;
    }
    .btn-submit-fb {
        background: linear-gradient(135deg, #1877f2, #0d6efd);
        border: none;
        color: white;
        padding: 12px 28px;
        font-weight: 600;
        font-size: 14px;
        border-radius: 40px;
        box-shadow: 0 4px 15px rgba(24, 119, 242, 0.35);
        transition: all 0.25s ease;
    }
    .btn-submit-fb:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(24, 119, 242, 0.45);
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
        background: #1877f2;
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
                    Facebook Pixel & Conversion API (CAPI)
                </h4>
                <p class="text-muted font-size-13 mb-0">
                    ব্রাউজার সাইড (Pixel) এবং সার্ভার সাইড (CAPI) ট্র্যাকিং একই সাথে কনফিগার করুন।
                </p>
            </div>
            
            {{-- Status Indicators --}}
            <div class="status-badge-box">
                @if($setting->status && !empty($setting->pixel_id))
                    <span class="status-pill status-pill-success">
                        <span class="status-dot status-dot-active"></span> Browser Pixel: Active
                    </span>
                    @if(!empty($setting->access_token))
                    <span class="status-pill status-pill-success">
                        <span class="status-dot status-dot-active"></span> Server CAPI: Active
                    </span>
                    @else
                    <span class="status-pill status-pill-muted">
                        <span class="status-dot status-dot-inactive"></span> Server CAPI: Token Needed
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
                        <span class="platform-icon-fb">
                            <i class="fab fa-facebook-f"></i>
                        </span>
                        Facebook Credentials Configuration
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.facebook_capi.update') }}" method="POST">
                        @csrf

                        {{-- Pixel ID --}}
                        <div class="mb-4">
                            <label class="form-label" for="pixel_id">
                                Facebook Pixel ID <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('pixel_id') is-invalid @enderror"
                                id="pixel_id"
                                name="pixel_id"
                                value="{{ old('pixel_id', $setting->pixel_id ?? '') }}"
                                placeholder="যেমন: 1755255695877942"
                                required
                            >
                            <small class="small-help">
                                <i class="fas fa-info-circle me-1 text-primary"></i>
                                এই Pixel ID টি স্বয়ংক্রিয়ভাবে ব্রাউজার-সাইড (Pixel Code) এবং সার্ভার-সাইড (CAPI) উভয় স্থানে ব্যবহৃত হবে।
                            </small>
                            @error('pixel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Access Token --}}
                        <div class="mb-4">
                            <label class="form-label" for="access_token">
                                Conversion API (CAPI) Access Token
                            </label>
                            <textarea
                                class="form-control @error('access_token') is-invalid @enderror"
                                id="access_token"
                                name="access_token"
                                rows="3"
                                placeholder="Facebook Developer/Events Manager থেকে জেনারেট করা Long-Lived Access Token এখানে দিন..."
                            >{{ old('access_token', $setting->access_token ?? '') }}</textarea>
                            <small class="small-help">
                                সার্ভার সাইড ট্র্যাকিংয়ের জন্য আবশ্যক। Events Manager &gt; Settings &gt; Set up directly &gt; Generate access token থেকে পাবেন।
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
                                value="{{ old('test_event_code', $setting->test_event_code ?? '') }}"
                                placeholder="যেমন: TEST61875"
                            >
                            <small class="small-help">
                                Events Manager &gt; Test Events ট্যাবে প্রদর্শিত কোডটি দিন। লাইভ অর্ডারের সময় এটি খালি রাখবেন।
                            </small>
                            @error('test_event_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status Switch --}}
                        <div class="mb-4 p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">Facebook Tracking Status</h6>
                                <small class="text-muted">সুইচটি চালু রাখলে ব্রাউজার পিক্সেল এবং সার্ভার CAPI দুটোই সক্রিয় থাকবে।</small>
                            </div>
                            <label class="switch">
                                <input
                                    type="checkbox"
                                    id="status"
                                    name="status"
                                    value="1"
                                    {{ old('status', $setting->status ?? 1) ? 'checked' : '' }}
                                >
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-submit-fb">
                                <i class="fas fa-check-circle me-1"></i> Save Facebook Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Guide / Instructions Card --}}
        <div class="col-lg-5 col-xl-4 mt-3 mt-lg-0">
            <div class="info-card">
                <h6><i class="fab fa-facebook text-primary me-2"></i>কীভাবে সেটআপ করবেন?</h6>

                <div class="info-step">
                    <div class="info-step-num">১</div>
                    <div><strong>Events Manager-এ যান:</strong> আপনার Facebook Business Manager থেকে Events Manager ওপেন করুন।</div>
                </div>

                <div class="info-step">
                    <div class="info-step-num">২</div>
                    <div><strong>Pixel ID কপি করুন:</strong> Settings ট্যাবে গিয়ে "Dataset ID" বা "Pixel ID" কপি করে এখানে বসান।</div>
                </div>

                <div class="info-step">
                    <div class="info-step-num">৩</div>
                    <div><strong>Access Token নিন:</strong> Settings ট্যাবের "Conversions API" সেকশন থেকে "Generate access token" এ ক্লিক করে টোকেনটি কপি করুন।</div>
                </div>

                <div class="info-step">
                    <div class="info-step-num">৪</div>
                    <div><strong>সেভ করুন:</strong> সেটিংস সেভ করলেই ব্রাউজার পিক্সেল এবং সার্ভার CAPI দুটোই লাইভ হয়ে যাবে। Deduplication স্বয়ংক্রিয়ভাবে হ্যান্ডেল হবে।</div>
                </div>

                <hr class="my-3 text-muted">

                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-shield-alt text-success fs-5"></i>
                    <small class="text-muted">
                        Browser Side ও Server Side ইভেন্ট একই <code>event_id</code> দ্বারা ডি-ডুপ্লিকেট করা হয়।
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
