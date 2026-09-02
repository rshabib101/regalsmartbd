@extends('backEnd.layouts.master')
@section('title','Facebook Product Catalog Data Feed')

@section('css')
<style>
    .catalog-hero-card {
        background: linear-gradient(135deg, #1877f2 0%, #0d6efd 100%);
        border-radius: 16px;
        color: #fff;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(24, 119, 242, 0.25);
        margin-bottom: 25px;
    }
    .catalog-card {
        border: none;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        margin-bottom: 24px;
    }
    .catalog-card-header {
        background: #fff;
        border-bottom: 1px solid #edf2f7;
        padding: 20px 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .catalog-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .url-copy-box {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .url-input {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 14px;
        color: #0f172a;
        font-weight: 600;
        flex-grow: 1;
    }
    .btn-copy-url {
        background: #1877f2;
        color: #fff;
        border: none;
        padding: 12px 22px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-copy-url:hover {
        background: #0d6efd;
        color: #fff;
        transform: translateY(-1px);
    }
    .btn-download-csv {
        background: #059669;
        color: #fff;
        border: none;
        padding: 12px 22px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-download-csv:hover {
        background: #047857;
        color: #fff;
        transform: translateY(-1px);
    }
    .stat-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s;
    }
    .stat-box:hover {
        background: #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border-color: #cbd5e1;
    }
    .stat-number {
        font-size: 26px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .guide-step {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
        align-items: flex-start;
    }
    .guide-num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #1877f2;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        flex-shrink: 0;
    }
    .guide-content h6 {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
        font-size: 14px;
    }
    .guide-content p {
        font-size: 13px;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="page-title mb-0" style="font-weight: 700; color: #1e293b;">
                    Facebook Product Catalog Data Feed
                </h4>
                <p class="text-muted font-size-13 mb-0">
                    Meta Commerce Manager / Facebook Shop-এর জন্য স্বয়ংক্রিয় রিয়েল-টাইম প্রোডাক্ট ফিড লিংক।
                </p>
            </div>
            <div>
                <a href="{{ $feedUrl }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-external-link-alt me-1"></i> Preview Raw Feed
                </a>
            </div>
        </div>
    </div>

    {{-- Main Feed URL Box Card --}}
    <div class="row">
        <div class="col-12">
            <div class="catalog-card">
                <div class="catalog-card-header">
                    <h5 class="catalog-card-title">
                        <i class="fas fa-rss text-primary fs-5"></i>
                        Your Meta Commerce Data Feed URL
                    </h5>
                    <span class="badge bg-success-lighten text-success font-size-12 px-3 py-1 rounded-pill">
                        <i class="fas fa-check-circle me-1"></i> Live Real-Time Feed
                    </span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-3 font-size-13">
                        নিচের ফিড লিংকটি কপি করে Meta Commerce Manager-এর <strong>Data Sources &gt; Data Feed &gt; Scheduled Feed</strong>-এ বসিয়ে দিন। ফেসবুক স্বয়ংক্রিয়ভাবে প্রতিদিন বা প্রতি ঘণ্টায় আপনার ওয়েবসাইটের নতুন প্রোডাক্ট, দাম এবং স্টক আপডেট করে নেবে।
                    </p>

                    <div class="url-copy-box mb-3">
                        <span class="badge bg-primary me-2">CSV Feed</span>
                        <input
                            type="text"
                            class="url-input"
                            id="catalogFeedUrl"
                            value="{{ $feedUrl }}"
                            readonly
                        >
                        <button type="button" class="btn-copy-url" onclick="copyCatalogUrl('catalogFeedUrl', 'copyIcon', 'copyText')">
                            <i class="far fa-copy" id="copyIcon"></i>
                            <span id="copyText">Copy CSV</span>
                        </button>
                        <a href="{{ route('admin.facebook_catalog.download') }}" class="btn-download-csv">
                            <i class="fas fa-download"></i>
                            <span>Download CSV</span>
                        </a>
                    </div>

                    <div class="url-copy-box">
                        <span class="badge bg-success me-2">XML RSS Feed</span>
                        <input
                            type="text"
                            class="url-input"
                            id="catalogXmlFeedUrl"
                            value="{{ $xmlFeedUrl ?? url('facebook-catalog.xml') }}"
                            readonly
                        >
                        <button type="button" class="btn-copy-url" style="background:#059669;" onclick="copyCatalogUrl('catalogXmlFeedUrl', 'copyXmlIcon', 'copyXmlText')">
                            <i class="far fa-copy" id="copyXmlIcon"></i>
                            <span id="copyXmlText">Copy XML</span>
                        </button>
                        <a href="{{ route('admin.facebook_catalog.download_xml') }}" class="btn-download-csv" style="background:#059669;">
                            <i class="fas fa-download"></i>
                            <span>Download XML</span>
                        </a>
                        <a href="{{ url('facebook-catalog.xml') }}" target="_blank" class="btn-download-csv" style="background:#4b5563;">
                            <i class="fas fa-external-link-alt"></i>
                            <span>View XML</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row mb-3">
        <div class="col-md-3 col-6 mb-3">
            <div class="stat-box">
                <div class="stat-number text-primary">{{ $totalProducts }}</div>
                <div class="stat-label">Total Active Products</div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="stat-box">
                <div class="stat-number text-success">{{ $inStockProducts }}</div>
                <div class="stat-label">In-Stock Products</div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="stat-box">
                <div class="stat-number text-info">{{ $totalCategories }}</div>
                <div class="stat-label">Product Categories</div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="stat-box">
                <div class="stat-number text-dark">BDT</div>
                <div class="stat-label">Feed Currency</div>
            </div>
        </div>
    </div>

    {{-- Setup Guide & Format Specification --}}
    <div class="row">
        {{-- Instructions --}}
        <div class="col-lg-7">
            <div class="catalog-card h-100">
                <div class="catalog-card-header">
                    <h5 class="catalog-card-title">
                        <i class="fab fa-facebook text-primary fs-5"></i>
                        কীভাবে Facebook Catalog-এ যুক্ত করবেন? (Meta Setup Guide)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="guide-step">
                        <div class="guide-num">১</div>
                        <div class="guide-content">
                            <h6>Meta Commerce Manager-এ প্রবেশ করুন</h6>
                            <p>আপনার ব্রাউজারে <a href="https://business.facebook.com/commerce" target="_blank" class="text-primary fw-bold">business.facebook.com/commerce</a> লিংকে যান এবং আপনার ক্যাটালগ নির্বাচন করুন।</p>
                        </div>
                    </div>

                    <div class="guide-step">
                        <div class="guide-num">২</div>
                        <div class="guide-content">
                            <h6>Data Sources মেনুতে যান</h6>
                            <p>বামপাশের মেনু থেকে <strong>Catalog &gt; Data Sources</strong> এ ক্লিক করুন এবং <strong>Add Items</strong> বাটনে চাপ দিন।</p>
                        </div>
                    </div>

                    <div class="guide-step">
                        <div class="guide-num">৩</div>
                        <div class="guide-content">
                            <h6>Data Feed (সিডিউলড ফিড) নির্বাচন করুন</h6>
                            <p>আইটেম যোগ করার অপশনগুলোর মধ্য থেকে <strong>Data Feed</strong> বা <strong>Use a URL</strong> সিলেক্ট করে Next চাপুন।</p>
                        </div>
                    </div>

                    <div class="guide-step">
                        <div class="guide-num">৪</div>
                        <div class="guide-content">
                            <h6>ফিড URL পেস্ট করুন এবং শিডিউল সেট করুন</h6>
                            <p>উপরের কপি করা ফিড URL টি বসিয়ে দিন। শিডিউল হিসেবে <strong>Hourly</strong> বা <strong>Daily</strong> নির্বাচন করুন এবং কারেন্সি <strong>BDT</strong> রাখুন।</p>
                        </div>
                    </div>

                    <div class="guide-step mb-0">
                        <div class="guide-num">৫</div>
                        <div class="guide-content">
                            <h6>Save &amp; Upload</h6>
                            <p>Save বাটনে ক্লিক করলেই ফেসবুক কিছুক্ষণের মধ্যে আপনার ওয়েবসাইটের সব প্রোডাক্ট ক্যাটালগে ইম্পোর্ট করে নিবে এবং ডায়নামিক অ্যাড রান করার জন্য প্রস্তুত হয়ে যাবে।</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Meta Catalog Field Specs --}}
        <div class="col-lg-5">
            <div class="catalog-card h-100">
                <div class="catalog-card-header">
                    <h5 class="catalog-card-title">
                        <i class="fas fa-check-double text-success fs-5"></i>
                        ফিডের কলাম ও ফরম্যাট
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="font-size-13 text-muted mb-3">
                        আপনার ফিডটি মেটা কমার্স ম্যানেজারের অফিসিয়াল স্পেসিফিকেশন ও Saleecom স্ট্যান্ডার্ড অনুযায়ী প্রস্তুত করা হয়েছে:
                    </p>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered font-size-12 mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>কলামের নাম</th>
                                    <th>বিবরণ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>id</code></td>
                                    <td>প্রোডাক্ট / ভ্যারিয়েন্ট ইউনিক আইডি</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td>প্রোডাক্টের নাম</td>
                                </tr>
                                <tr>
                                    <td><code>item_group_id</code></td>
                                    <td>সাইজ/ভ্যারিয়েন্ট গ্রুপিং আইডি</td>
                                </tr>
                                <tr>
                                    <td><code>description</code></td>
                                    <td>প্রোডাক্টের বিবরণ (HTML মুক্ত)</td>
                                </tr>
                                <tr>
                                    <td><code>availability</code></td>
                                    <td><code>in stock</code> অথবা <code>out of stock</code></td>
                                </tr>
                                <tr>
                                    <td><code>price</code></td>
                                    <td>নিয়মিত মূল্য (যেমন: 1500 BDT)</td>
                                </tr>
                                <tr>
                                    <td><code>sale_price</code></td>
                                    <td>অফার মূল্য (যেমন: 790 BDT)</td>
                                </tr>
                                <tr>
                                    <td><code>link</code></td>
                                    <td>প্রোডাক্টের ওয়েবসাইট লিংক</td>
                                </tr>
                                <tr>
                                    <td><code>image_link</code></td>
                                    <td>মূল ছবির সম্পূর্ণ URL</td>
                                </tr>
                                <tr>
                                    <td><code>brand</code></td>
                                    <td>ব্র্যান্ড / ওয়েবসাইটের নাম</td>
                                </tr>
                                <tr>
                                    <td><code>fb_product_category</code></td>
                                    <td>ক্যাটাগরির নাম</td>
                                </tr>
                                <tr>
                                    <td><code>size</code></td>
                                    <td>সাইজ (ভ্যারিয়েন্ট থাকলে)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function copyCatalogUrl(inputId, iconId, textId) {
        inputId = inputId || 'catalogFeedUrl';
        iconId  = iconId  || 'copyIcon';
        textId  = textId  || 'copyText';
        const input = document.getElementById(inputId);
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(function() {
            const btnText = document.getElementById(textId);
            const icon = document.getElementById(iconId);
            btnText.innerText = 'Copied!';
            icon.className = 'fas fa-check';
            
            setTimeout(() => {
                btnText.innerText = (inputId === 'catalogFeedUrl') ? 'Copy CSV' : 'Copy XML';
                icon.className = 'far fa-copy';
            }, 3000);
        });
    }
</script>
@endsection
