@extends('backEnd.layouts.master')
@section('title','Create New Product')

@section('css')
<link href="{{asset('public/backEnd')}}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
<link href="{{asset('public/backEnd')}}/assets/libs/summernote/summernote-lite.min.css" rel="stylesheet" type="text/css" />
<style>
    /* কাস্টম ডিজাইন */
    .section-title { background: #f1f3f7; padding: 10px 15px; border-radius: 6px; font-weight: 700; color: #343a40; border-left: 4px solid #727cf5; margin-bottom: 20px; font-size: 15px; }
    .form-label { font-weight: 600; font-size: 13px; color: #555; }
    .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 0.75rem; }
    
    /* ভ্যারিয়েন্ট আইটেম ডিজাইন */
    .variant-card { background: #fafbfd; border: 1px solid #e2e7f1; padding: 15px; border-radius: 10px; margin-bottom: 12px; position: relative; }
    
    /* টগল সুইচ */
    .switch { position: relative; display: inline-block; width: 40px; height: 20px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
    .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #0acf97; }
    input:checked + .slider:before { transform: translateX(20px); }

    .btn-remove-row { margin-top: 28px; }
</style>
@endsection 

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between py-3">
                <h4 class="page-title mb-0">Add New Product</h4>
                <div class="page-title-right">
                    <a href="{{route('products.index')}}" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="fe-list me-1"></i> Manage Products</a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{route('products.store')}}" method="POST" data-parsley-validate="" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="section-title"><i class="fe-info me-1"></i> Basic Information</div>
                        
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Enter product name" required />
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Main Category *</label>
                                <select class="form-control select2" name="category_id" id="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{$category->id}}">{{$category->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sub Category</label>
                                <select class="form-control select2" name="subcategory_id" id="subcategory_id">
                                    <option value="">Choose Sub Category</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Child Category</label>
                                <select class="form-control select2" name="childcategory_id" id="childcategory_id">
                                    <option value="">Choose Child Category</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Full Description *</label>
                            <textarea name="description" class="summernote" required>{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label">Short Note</label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Small note for internal use..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="d-block form-label">Wholesale Product</label>
                            <label class="switch"><input type="checkbox" value="1" name="is_wholesale" id="is_wholesale"><span class="slider round"></span></label>
                        </div>
                    </div>
                </div>

                <div id="wholesale_area" style="display:none;" class="card mb-4">
                    <div class="card-body">
                        <div class="section-title d-flex justify-content-between align-items-center">
                            <span><i class="fe-dollar-sign me-1"></i> Wholesale Pricing Tiers</span>
                            <button type="button" class="btn btn-sm btn-success add-wholesale-tier rounded-pill px-3"><i class="fa fa-plus me-1"></i> Add New Tier</button>
                        </div>
                        
                        <div id="wholesale-wrapper">
                            <div class="variant-card">
                                <div class="row align-items-end">
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Min Quantity</label>
                                        <input type="number" name="wholesale_price[0][min_quantity]" class="form-control" placeholder="e.g. 10">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Max Quantity</label>
                                        <input type="number" name="wholesale_price[0][max_quantity]" class="form-control" placeholder="e.g. 50 (optional)">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Wholesale Price</label>
                                        <input type="number" step="0.01" name="wholesale_price[0][wholesale_price]" class="form-control" placeholder="0.00">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Stock Qty</label>
                                        <input type="number" name="wholesale_price[0][stock]" class="form-control" placeholder="0">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-success add-wholesale-tier w-100"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4" id="variant_section">
                    <div class="card-body">
                        <div class="section-title d-flex justify-content-between align-items-center mb-2">
                            <span><i class="fe-layers me-1"></i> Product Variants (Color & Size)</span>
                            <button type="button" class="btn btn-sm btn-success add-variant-btn rounded-pill px-3 shadow-sm">
                                <i class="fa fa-plus me-1"></i> Add Variant
                            </button>
                        </div>
                        <p class="text-muted font-13 mb-3">
                            প্রত্যেকটি ভ্যারিয়েন্টের জন্য আলাদা সাইজ, কালার, মূল্য, স্টক এবং ছবি নির্ধারণ করতে পারেন।
                        </p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-centered mb-0" id="variant-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 140px;">Color</th>
                                        <th style="min-width: 140px;">Size</th>
                                        <th style="min-width: 110px;">Price *</th>
                                        <th style="min-width: 90px;">Stock *</th>
                                        <th style="min-width: 110px;">SKU</th>
                                        <th style="min-width: 180px;">Variant Image</th>
                                        <th style="width: 50px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="variant-tbody">
                                    <tr class="variant-row" data-row="0">
                                        <td>
                                            <select name="variant_price[0][color_id]" class="form-control form-select variant-color-select">
                                                <option value="">No Color</option>
                                                @foreach($colors as $color)
                                                    <option value="{{ $color->id }}">{{ $color->colorName ?? $color->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="variant_price[0][size_id]" class="form-control form-select variant-size-select">
                                                <option value="">No Size</option>
                                                @foreach($sizes as $size)
                                                    <option value="{{ $size->id }}">{{ $size->sizeName ?? $size->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="variant_price[0][price]" class="form-control form-control-sm variant-price-input" placeholder="Price">
                                        </td>
                                        <td>
                                            <input type="number" min="0" name="variant_price[0][stock]" value="100" class="form-control form-control-sm" placeholder="Stock">
                                        </td>
                                        <td>
                                            <input type="text" name="variant_price[0][sku]" class="form-control form-control-sm" placeholder="SKU">
                                        </td>
                                        <td>
                                            <input type="file" name="variant_image[0][image]" class="form-control form-control-sm variant-img-input" accept="image/*">
                                            <div class="variant-img-preview mt-1" style="display:none;">
                                                <img src="" alt="Preview" class="rounded border" style="max-width:38px;max-height:38px;object-fit:cover;">
                                                <button type="button" class="btn btn-xs btn-outline-danger variant-img-clear ms-1 py-0 px-1"><i class="fe-x"></i></button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-variant-btn" title="Remove variant">
                                                <i class="fe-trash-2"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Hidden Template for Dynamic Row Addition --}}
                        <template id="variant-row-template">
                            <tr class="variant-row" data-row="__INDEX__">
                                <td>
                                    <select name="variant_price[__INDEX__][color_id]" class="form-control form-select variant-color-select">
                                        <option value="">No Color</option>
                                        @foreach($colors as $color)
                                            <option value="{{ $color->id }}">{{ $color->colorName ?? $color->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="variant_price[__INDEX__][size_id]" class="form-control form-select variant-size-select">
                                        <option value="">No Size</option>
                                        @foreach($sizes as $size)
                                            <option value="{{ $size->id }}">{{ $size->sizeName ?? $size->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="variant_price[__INDEX__][price]"
                                           class="form-control form-control-sm variant-price-input" placeholder="Price">
                                </td>
                                <td>
                                    <input type="number" min="0" name="variant_price[__INDEX__][stock]"
                                           value="100" class="form-control form-control-sm" placeholder="Stock">
                                </td>
                                <td>
                                    <input type="text" name="variant_price[__INDEX__][sku]"
                                           class="form-control form-control-sm" placeholder="SKU">
                                </td>
                                <td>
                                    <input type="file" name="variant_image[__INDEX__][image]" class="form-control form-control-sm variant-img-input" accept="image/*">
                                    <div class="variant-img-preview mt-1" style="display:none;">
                                        <img src="" alt="Preview" class="rounded border" style="max-width:38px;max-height:38px;object-fit:cover;">
                                        <button type="button" class="btn btn-xs btn-outline-danger variant-img-clear ms-1 py-0 px-1"><i class="fe-x"></i></button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-variant-btn" title="Remove variant">
                                        <i class="fe-trash-2"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="section-title"><i class="fe-search me-1"></i> SEO Configuration</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Meta Title</label>
                                <input type="text" name="meta_title" class="form-control" placeholder="SEO optimized title">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Meta Keywords</label>
                                <input type="text" name="meta_keywords" class="form-control" placeholder="keyword1, keyword2">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="2" placeholder="Brief description for search engines"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Meta Image</label>
                                <input type="file" name="meta_image" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="section-title"><i class="fe-dollar-sign me-1"></i> Pricing & Inventory</div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Purchase Price <small class="text-muted">(Optional)</small></label>
                            <input type="number" name="purchase_price" class="form-control border-primary" placeholder="0">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Old Price</label>
                                <input type="number" name="old_price" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Price <small class="text-muted">(Optional)</small></label>
                                <input type="number" name="new_price" class="form-control font-weight-bold" placeholder="0">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Reseller Price</label>
                            <input type="number" step="0.01" name="reseller_price" class="form-control" placeholder="Reseller price (optional)">
                            <small class="text-muted">Special price for resellers. Leave empty if not applicable.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Stock <small class="text-muted">(Optional)</small></label>
                                <input type="number" name="stock" class="form-control" placeholder="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit (kg/pc)</label>
                                <input type="text" name="pro_unit" class="form-control" placeholder="e.g. pcs">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="section-title"><i class="fe-image me-1"></i> Media & Video</div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Product Gallery Images *</label>
                            <div class="increment-wrapper">
                                <div class="control-group increment mb-2 image-row">
                                    <div class="row align-items-end g-2">
                                        <div class="col-md-10">
                                            <label class="form-label small">Image</label>
                                            <input type="file" name="image[]" class="form-control form-control-sm" accept="image/*">
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-success btn-increment btn-sm w-100" type="button"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clone d-none">
                                <div class="control-group mt-2 image-row">
                                    <div class="row align-items-end g-2">
                                        <div class="col-md-10">
                                            <label class="form-label small">Image</label>
                                            <input type="file" name="image[]" class="form-control form-control-sm" accept="image/*">
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-danger btn-remove-image btn-sm w-100" type="button"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== VIDEO SECTION ===== --}}
                        <div class="form-group mb-0">
                            <label class="form-label fw-semibold">প্রোডাক্ট ভিডিও</label>
                            {{-- Source selector --}}
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="pro_video_source"
                                           id="vs_yt_c" value="youtube" checked>
                                    <label class="form-check-label" for="vs_yt_c">
                                        <i class="fa fa-youtube-play text-danger me-1"></i> YouTube লিংক
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="pro_video_source"
                                           id="vs_up_c" value="upload">
                                    <label class="form-check-label" for="vs_up_c">
                                        <i class="fa fa-upload text-primary me-1"></i> ভিডিও আপলোড
                                    </label>
                                </div>
                            </div>

                            {{-- YouTube input --}}
                            <div id="yt_section_c">
                                <input type="text" name="pro_video" id="pro_video_c" class="form-control"
                                       placeholder="YouTube URL বা Video ID দিন (যেমন: https://youtu.be/xxxxx)">
                                <div id="yt_preview_c" class="mt-2" style="display:none;">
                                    <iframe id="yt_iframe_c" width="100%" height="200"
                                            src="" frameborder="0" allowfullscreen
                                            style="border-radius:8px;"></iframe>
                                </div>
                                <small class="text-muted">YouTube full URL অথবা শুধু Video ID উভয়ই চলবে।</small>
                            </div>

                            {{-- Upload input --}}
                            <div id="up_section_c" style="display:none;">
                                <input type="file" name="pro_video_file" id="pro_video_file_c"
                                       class="form-control" accept="video/mp4,video/webm,video/ogg">
                                <div id="up_preview_c" class="mt-2" style="display:none;">
                                    <video id="up_video_c" width="100%" height="220" controls
                                           style="border-radius:8px;background:#000;"></video>
                                </div>
                                <small class="text-muted">MP4, WebM, OGG সাপোর্টেড। সর্বোচ্চ 40MB (php.ini সেটিং)।</small>
                            </div>
                        </div>
                        {{-- ===== /VIDEO SECTION ===== --}}
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="section-title"><i class="fe-settings me-1"></i> Product Settings</div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Product Type</label>
                            <select class="form-control bg-light" id="product_type" name="product_type">
                                <option value="physical" selected>Physical Product</option>
                                <option value="digital">Digital Product</option>
                            </select>
                        </div>

                        <div class="form-group mb-3" id="advance_area">
                            <label class="form-label">Advance Payment Amount</label>
                            <input type="number" name="advance_amount" class="form-control" placeholder="0.00">
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Free Delivery</label>
                            <div class="d-flex align-items-center">
                                <label class="switch me-3">
                                    <input type="checkbox" value="1" name="free_delivery">
                                    <span class="slider round"></span>
                                </label>
                                <small class="text-muted">Enable free delivery for this product (No shipping charge will be applied)</small>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Facebook Catalog</label>
                            <div class="d-flex align-items-center">
                                <label class="switch me-3">
                                    <input type="checkbox" value="1" name="in_facebook_catalog" {{ old('in_facebook_catalog', 1) ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                                <small class="text-muted">Include this product in Facebook Catalog Data Feed</small>
                            </div>
                        </div>

                        <div id="digital_area" style="display:none;" class="p-2 border rounded mb-3 bg-light">
                            <label class="form-label">Digital File</label>
                            <input type="file" class="form-control mb-2" name="digital_file">
                            <div class="row">
                                <div class="col-6">
                                    <small>Limit</small>
                                    <input type="number" class="form-control form-control-sm" name="download_limit" value="5">
                                </div>
                                <div class="col-6">
                                    <small>Days Exp.</small>
                                    <input type="number" class="form-control form-control-sm" name="download_expire_days" value="7">
                                </div>
                            </div>
                        </div>

                        <div class="row text-center mb-3">
                            <div class="col-4 mb-2">
                                <label class="d-block form-label">Status</label>
                                <label class="switch"><input type="checkbox" value="1" name="status" checked><span class="slider round"></span></label>
                            </div>
                            <div class="col-4 mb-2">
                                <label class="d-block form-label">Hot Deal</label>
                                <label class="switch"><input type="checkbox" value="1" name="topsale"><span class="slider round"></span></label>
                            </div>
                           
                            <div class="col-6">
                                <label class="d-block form-label">Flash Sale</label>
                                <label class="switch"><input type="checkbox" value="1" name="flashsale"><span class="slider round"></span></label>
                            </div>
                             <div class="col-6">
                                <label class="form-label">Brand</label>
                                <select class="form-control select2" name="brand_id">
                                    <option value="">None</option>
                                    @foreach($brands as $value)
                                        <option value="{{$value->id}}">{{$value->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 shadow rounded-pill"><i class="fe-check-circle me-1"></i> Publish Product</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection 

@section('script')
<script src="{{asset('public/backEnd/')}}/assets/libs/parsleyjs/parsley.min.js"></script>
<script src="{{asset('public/backEnd/')}}/assets/libs/select2/js/select2.min.js"></script>
<script src="{{asset('public/backEnd/')}}/assets/libs/summernote/summernote-lite.min.js"></script>

<script>
    $(document).ready(function () {
        $('.select2').select2({ width: '100%' });
        $(".summernote").summernote({ height: 200, placeholder: "Describe your product..." });

        // Image Increment
        $(".btn-increment").click(function () {
            var html = $(".clone").html();
            $(".increment-wrapper").append(html);
        });
        $("body").on("click", ".btn-remove-image", function () {
            $(this).parents(".control-group").remove();
        });

        // Product Type Toggle
        $('#product_type').change(function(){
            let type = $(this).val();
            if(type === 'digital'){
                $('#digital_area').slideDown();
                $('#advance_area').slideUp();
                $('#variant_section').slideUp();
            } else {
                $('#digital_area').slideUp();
                $('#advance_area').slideDown();
                $('#variant_section').slideDown();
            }
        });

        // Dynamic Variant Add/Remove
        let currentVariantIndex = 1;

        $(document).on('click', '.add-variant-btn', function() {
            let template = document.getElementById('variant-row-template').innerHTML;
            let newRowHtml = template.replace(/__INDEX__/g, currentVariantIndex);
            $('#variant-tbody').append(newRowHtml);
            currentVariantIndex++;
        });

        $(document).on('click', '.remove-variant-btn', function() {
            $(this).closest('tr.variant-row').remove();
        });

        // Auto-sync 1st variant price with New Price input
        $(document).on('input', '.variant-price-input', function() {
            let firstPrice = $('.variant-price-input').filter(function() { return $(this).val(); }).first().val();
            let $newPrice = $('input[name="new_price"]');
            if (firstPrice && (!$newPrice.val() || $newPrice.data('auto-synced'))) {
                $newPrice.val(firstPrice).data('auto-synced', true);
            }
        });

        // Variant Image Preview & Clear
        $(document).on('change', '.variant-img-input', function() {
            var $input = $(this);
            var $preview = $input.siblings(".variant-img-preview");
            var $img = $preview.find("img");
            var file = this.files[0];
            if (file && file.type.startsWith("image/")) {
                var reader = new FileReader();
                reader.onload = function(e) { $img.attr("src", e.target.result); $preview.show(); };
                reader.readAsDataURL(file);
            } else {
                $preview.hide();
                $img.attr("src", "");
            }
        });

        $(document).on('click', '.variant-img-clear', function() {
            var $preview = $(this).closest(".variant-img-preview");
            $preview.siblings(".variant-img-input").val("");
            $preview.find("img").attr("src", "");
            $preview.hide();
        });

        // Wholesale toggle
        $("#is_wholesale").on("change", function () {
            if ($(this).is(':checked')) {
                $("#wholesale_area").slideDown();
                $("#wholesale_area input").prop('required', true);
            } else {
                $("#wholesale_area").slideUp();
                $("#wholesale_area input").prop('required', false);
            }
        });

        // Wholesale pricing tiers
        let wholesaleIndex = 1;
        $("body").on("click", ".add-wholesale-tier", function () {
            let wrapper = $("#wholesale-wrapper");
            let firstRow = wrapper.find(".variant-card").first().clone();
            
            firstRow.find('input').each(function(){
                let oldName = $(this).attr('name');
                $(this).attr('name', oldName.replace(/\[\d+\]/, '[' + wholesaleIndex + ']'));
                $(this).val('');
            });

            // Change add button to remove button
            firstRow.find('.add-wholesale-tier').removeClass('btn-success add-wholesale-tier').addClass('btn-danger btn-remove-wholesale').html('<i class="fa fa-trash"></i>');
            wrapper.append(firstRow);
            wholesaleIndex++;
        });

        $("body").on("click", ".btn-remove-wholesale", function () {
            $(this).parents(".variant-card").remove();
        });

        // AJAX Categories
        $("#category_id").on("change", function () {
            var id = $(this).val();
            if (id) {
                $.get("{{url('ajax-product-subcategory')}}?category_id=" + id, function(res){
                    $("#subcategory_id").empty().append('<option value="">Choose Sub Category</option>');
                    $.each(res, function(key, value){
                        $("#subcategory_id").append('<option value="'+key+'">'+value+'</option>');
                    });
                });
            }
        });

        $("#subcategory_id").on("change", function () {
            var id = $(this).val();
            if (id) {
                $.get("{{url('ajax-product-childcategory')}}?subcategory_id=" + id, function(res){
                    $("#childcategory_id").empty().append('<option value="">Choose Child Category</option>');
                    $.each(res, function(key, value){
                        $("#childcategory_id").append('<option value="'+key+'">'+value+'</option>');
                    });
                });
            }
        });
    });

    // ===== VIDEO SOURCE SWITCHER (Create) =====
    (function () {
        var radios   = document.querySelectorAll('input[name="pro_video_source"]');
        var ytSec    = document.getElementById('yt_section_c');
        var upSec    = document.getElementById('up_section_c');

        function switchVideo(val) {
            if (val === 'upload') {
                ytSec.style.display = 'none';
                upSec.style.display = '';
            } else {
                ytSec.style.display = '';
                upSec.style.display = 'none';
            }
        }

        radios.forEach(function (r) {
            r.addEventListener('change', function () { switchVideo(this.value); });
        });

        // YouTube live preview
        var ytInput = document.getElementById('pro_video_c');
        if (ytInput) {
            ytInput.addEventListener('input', function () {
                var val = this.value.trim();
                var id  = extractYtId(val);
                var box = document.getElementById('yt_preview_c');
                var fr  = document.getElementById('yt_iframe_c');
                if (id) {
                    fr.src = 'https://www.youtube.com/embed/' + id;
                    box.style.display = '';
                } else {
                    fr.src = '';
                    box.style.display = 'none';
                }
            });
        }

        // Upload local preview
        var upInput = document.getElementById('pro_video_file_c');
        if (upInput) {
            upInput.addEventListener('change', function () {
                var file = this.files[0];
                var box  = document.getElementById('up_preview_c');
                var vid  = document.getElementById('up_video_c');
                if (file) {
                    vid.src = URL.createObjectURL(file);
                    box.style.display = '';
                } else {
                    vid.src = '';
                    box.style.display = 'none';
                }
            });
        }

        function extractYtId(input) {
            if (!input) return null;
            if (/^[a-zA-Z0-9_-]{11}$/.test(input)) return input;
            var m = input.match(/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
            return m ? m[1] : null;
        }
    })();
</script>
@endsection