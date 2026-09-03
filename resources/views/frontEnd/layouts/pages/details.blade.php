@extends('frontEnd.layouts.master')
@section('title', $details->name)
@php
    $pageType = 'product';
    $firstVariant = ($details->variantPrices && $details->variantPrices->count() > 0) ? $details->variantPrices->first() : null;
    $displayPrice = ($firstVariant && $firstVariant->price > 0) ? $firstVariant->price : ($details->new_price ?? 0);
    $firstVariantUnit = optional($firstVariant?->size)->sizeName ?? ($details->pro_unit ?? '');
@endphp
@push('dataLayer')
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({ ecommerce: null });
window.dataLayer.push({
    event: 'view_item',
    ecommerce: {
        currency: 'BDT',
        value: {{ (float) $displayPrice }},
        items: [{
            item_id: '{{ $details->id }}',
            item_name: @json($details->name),
            price: {{ (float) $displayPrice }},
            item_brand: @json(optional($details->brand)->name ?? ($generalsetting->name ?? '')),
            item_category: @json(optional($details->category)->name ?? ''),
            item_variant: @json($firstVariantUnit),
            quantity: 1
        }]
    }
});

@if(isset($products) && $products->count() > 0)
window.dataLayer.push({
    event: 'view_item_list',
    ecommerce: {
        item_list_name: 'Related Products',
        currency: 'BDT',
        items: [
            @foreach($products as $rp)
            {
                item_id: '{{ $rp->id }}',
                item_name: @json($rp->name),
                price: {{ (float) ($rp->new_price ?? 0) }},
                item_brand: @json(optional($rp->brand)->name ?? ''),
                item_category: @json(optional($rp->category)->name ?? ''),
                index: {{ $loop->iteration }}
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
});
@endif

if (typeof fbq === 'function') {
    fbq('track', 'ViewContent', {
        content_ids:      ['{{ $details->id }}'],
        content_name:     @json($details->name),
        content_category: @json(optional($details->category)->name ?? ''),
        content_type:     'product',
        value:            {{ (float) $displayPrice }},
        currency:         'BDT',
        // ⭐ Bhoorwear standard: contents, shipping, num_items
        contents:  [{ id: '{{ $details->id }}', quantity: 1, item_price: {{ (float) $displayPrice }} }],
        shipping:  0,
        num_items: 1
    }, { eventID: '{{ $vc_event_id ?? ("vc_prod_".$details->id."_".time()) }}' });
}
if (typeof ttq !== 'undefined') {
    ttq.track('ViewContent', {
        content_type: 'product',
        content_id: '{{ $details->id }}',
        content_name: @json($details->name),
        value: {{ (float) $displayPrice }},
        currency: 'BDT'
    });
}
@endpush
@push('seo')
@php
    $metaTitle = $details->meta_title ?? $details->name;
    $metaDescription = $details->meta_description ?? Str::limit(strip_tags($details->description), 160);
    $metaKeywords = $details->meta_keywords ?? $details->name;
    $metaImage = $details->meta_image ? asset($details->meta_image) : asset(optional($details->image)->image);
    $brandName = optional($details->brand)->name ?? ($generalsetting->name ?? 'Smart Grammo Bazar');
    $categoryName = optional($details->category)->name ?? 'General';
    $inStock = (!is_null($details->stock) && $details->stock < 1) ? false : true;
@endphp

<meta name="app-url" content="{{ route('product', $details->slug) }}" />
<meta name="robots" content="index, follow" />

<meta name="title" content="{{ $metaTitle }}" />
<meta name="description" content="{{ $metaDescription }}" />
<meta name="keywords" content="{{ $metaKeywords }}" />

<!-- Twitter Card data -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:site" content="@gomobd" />
<meta name="twitter:title" content="{{ $metaTitle }}" />
<meta name="twitter:description" content="{{ $metaDescription }}" />
<meta name="twitter:image" content="{{ $metaImage }}" />

<!-- Open Graph data -->
<meta property="og:title" content="{{ $metaTitle }}" />
<meta property="og:type" content="product" />
<meta property="og:url" content="{{ route('product', $details->slug) }}" />
<meta property="og:image" content="{{ $metaImage }}" />
<meta property="og:description" content="{{ $metaDescription }}" />
<meta property="og:site_name" content="{{ $generalsetting->name ?? 'Smart Grammo Bazar' }}" />

<!-- Facebook / Meta Product Catalog Microdata -->
<meta property="product:retailer_item_id" content="{{ $details->id }}" />
<meta property="product:price:amount" content="{{ $displayPrice }}" />
<meta property="product:price:currency" content="BDT" />
@if($details->old_price && $details->old_price > $displayPrice)
<meta property="product:sale_price:amount" content="{{ $displayPrice }}" />
<meta property="product:sale_price:currency" content="BDT" />
<meta property="product:original_price:amount" content="{{ $details->old_price }}" />
@endif
<meta property="product:availability" content="{{ $inStock ? 'in stock' : 'out of stock' }}" />
<meta property="product:condition" content="new" />
<meta property="product:brand" content="{{ $brandName }}" />
<meta property="product:category" content="{{ $categoryName }}" />

@php
    $schemaImages = [$metaImage];
    if ($details->images && $details->images->count() > 0) {
        foreach ($details->images->take(4) as $extraImg) {
            if (!empty($extraImg->image)) {
                $schemaImages[] = asset($extraImg->image);
            }
        }
    }

    $schemaData = [
        '@context'    => 'https://schema.org/',
        '@type'       => 'Product',
        'name'        => $details->name,
        'image'       => $schemaImages,
        'description' => $metaDescription,
        'sku'         => $details->product_code ?: (string)$details->id,
        'mpn'         => (string)$details->id,
        'brand'       => [
            '@type' => 'Brand',
            'name'  => $brandName,
        ],
        'category'    => $categoryName,
        'offers'      => [
            '@type'           => 'Offer',
            'url'             => route('product', $details->slug),
            'priceCurrency'   => 'BDT',
            'price'           => (string)$displayPrice,
            'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
            'itemCondition'   => 'https://schema.org/NewCondition',
            'availability'    => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller'          => [
                '@type' => 'Organization',
                'name'  => $generalsetting->name ?? 'Smart Grammo Bazar',
            ],
        ],
    ];

    if (isset($reviews) && $reviews->count() > 0 && isset($item_count) && $item_count > 0) {
        $schemaData['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format($reviews->avg('ratting'), 1),
            'reviewCount' => (string)$item_count,
            'bestRating'  => '5',
            'worstRating' => '1',
        ];
    }
@endphp

<!-- Schema.org JSON-LD Structured Data (Google & Meta Merchant standard) -->
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush


@push('css')
<link rel="stylesheet" href="{{ asset('public/frontEnd/css/zoomsl.css') }}">
<style>
/* ✅ Scoped Review Section */
.gomobd-review-section {
    font-family: 'Poppins', sans-serif;
}

/* Title */
.gomobd-review-section .gomobd-review-title {
    font-size: 20px;
    color: #222;
}

/* Review Card */
.gomobd-review-section .gomobd-review-card {
    background: #fff;
    border: 1px solid #e6e6e6;
    border-radius: 10px;
    padding: 16px 20px;
    transition: all 0.3s ease-in-out;
}
.gomobd-review-section .gomobd-review-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Header */
.gomobd-review-section .gomobd-review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

/* Avatar */
.gomobd-review-section .gomobd-review-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #198754;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
    margin-right: 12px;
}

/* Name + Date */
.gomobd-review-section .gomobd-review-meta {
    flex-grow: 1;
}
.gomobd-review-section .gomobd-review-name {
    font-size: 16px;
    margin: 0;
    color: #222;
}
.gomobd-review-section .gomobd-review-date {
    font-size: 13px;
    color: #888;
}

/* Stars */
.gomobd-review-section .gomobd-review-stars {
    color: #f8b400;
    font-size: 15px;
}

/* Review Text */
.gomobd-review-section .gomobd-review-body {
    margin-top: 10px;
    color: #555;
    font-size: 15px;
    line-height: 1.6;
}

/* Empty state */
.gomobd-review-section .gomobd-review-empty {
    background: #f9f9f9;
    border-radius: 10px;
    color: #777;
}

/* ✅ Simple Wholesale Pricing Styles */
.wholesale-tier-row:hover {
    background: #f0f8f0 !important;
}

.wholesale-tier-row.active-tier {
    background: #d4edda !important;
    border-left: 3px solid #28a745 !important;
}

/* Indicator Thumbnails */
.indicator-item {
    cursor: pointer;
    border: 2px solid #e0e0e0;
    transition: all 0.2s ease;
    border-radius: 6px;
    overflow: hidden;
}
.indicator-item:hover,
.indicator-item.active {
    border-color: #28a745 !important;
    box-shadow: 0 0 6px rgba(40, 167, 69, 0.4);
}

/* Review Modal: z-index fix যাতে ব্যাকড্রপের পিছনে না পড়ে এবং ক্লিক/টাইপ কাজ করে */
#exampleModal {
    z-index: 10055 !important;
}
#exampleModal .modal-dialog {
    z-index: 10056 !important;
}
</style>
@endpush

@section('content')
<div class="homeproduct main-details-page">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <section class="product-section">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-6 position-relative">
                                @if($details->old_price)
                                <div class="product-details-discount-badge">
                                    <div class="sale-badge">
                                        <div class="sale-badge-inner">
                                            <div class="sale-badge-box">
                                                <span class="sale-badge-text">
                                                    <p> @php $discount=(((($details->old_price)-($details->new_price))*100) / ($details->old_price)) @endphp {{ number_format($discount, 0) }}%</p>
                                                    ছাড়
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="details_slider owl-carousel" id="details_slider_main">
                                    @foreach ($details->images as $key => $value)
                                        <div class="dimage_item" data-id="{{ $key }}" data-color-id="{{ $value->color_id ?? '' }}" data-size-id="{{ $value->size_id ?? '' }}">
                                            <img src="{{ asset($value->image) }}" class="block__pic" />
                                        </div>
                                    @endforeach
                                </div>
                                <div
                                    class="indicator_thumb @if ($details->images->count() > 4) thumb_slider owl-carousel @endif" id="indicator_thumb_wrapper">
                                    @foreach ($details->images as $key => $image)
                                        <div class="indicator-item {{ $loop->first ? 'active' : '' }}" data-id="{{ $key }}" data-color-id="{{ $image->color_id ?? '' }}" data-size-id="{{ $image->size_id ?? '' }}">
                                            <img src="{{ asset($image->image) }}" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="details_right">
                                    <div class="breadcrumb">
                                        <ul>
                                            <li><a href="{{ url('/') }}">Home</a></li>
                                            <li><span>/</span></li>
                                            <li><a
                                                    href="{{ url('/category/' . $details->category->slug) }}">{{ $details->category->name }}</a>
                                            </li>
                                            @if ($details->subcategory)
                                                <li><span>/</span></li>
                                                <li><a
                                                        href="#">{{ $details->subcategory ? $details->subcategory->subcategoryName : '' }}</a>
                                                </li>
                                                @endif @if ($details->childcategory)
                                                    <li><span>/</span></li>
                                                    <li><a
                                                            href="#">{{ $details->childcategory->childcategoryName }}</a>
                                                    </li>
                                                @endif
                                        </ul>
                                    </div>

                                    <div class="product">
                                        <div class="product-cart">
                                            <p class="name">{{ $details->name }}</p>
                                            <p class="details-price">
                                                <del id="oldPrice" style="{{ ($details->old_price && $details->old_price > $displayPrice) ? '' : 'display:none;' }}">৳{{ $details->old_price }}</del>
                                                <span id="newPrice">৳{{ $displayPrice }}</span>
                                            </p>
                                            <div class="details-ratting-wrapper">
                                            @php
                                                $averageRating = $reviews->avg('ratting');
                                                $filledStars = floor($averageRating);
                                                $emptyStars = 5 - $filledStars;
                                            @endphp
                                            
                                            @if ($averageRating >= 0 && $averageRating <= 5)
                                                @for ($i = 1; $i <= $filledStars; $i++)
                                                    <i class="fas fa-star"></i>
                                                @endfor
                                            
                                                @if ($averageRating == $filledStars)
                                                    {{-- If averageRating is an integer, don't display half star --}}
                                                @else
                                                    <i class="far fa-star-half-alt"></i>
                                                @endif
                                            
                                                @for ($i = 1; $i <= $emptyStars; $i++)
                                                    <i class="far fa-star"></i>
                                                @endfor
                                            
                                                <span>{{ number_format($averageRating, 2) }}/5</span>
                                            @else
                                                <span>Invalid rating range</span>
                                            @endif
                                            <a class="all-reviews-button" href="#writeReview">See Reviews</a>
                                            </div>
                                            <div class="product-code">
                                                <p><span>প্রোডাক্ট কোড : </span>{{ $details->product_code }}</p>
                                            </div>

                                            {{-- ⭐⭐ এখানে Product Type দেখানো হচ্ছে ⭐⭐ --}}
                                            @php
                                                $productTypeText = $details->is_digital
                                                    ? 'Digital'
                                                    : 'Physical';
                                            @endphp
                                            <div class="pro_brand">
                                                <p>
                                                  Product Type: {{ $productTypeText }}
                                                </p>
                                            </div>
                                            {{-- ⭐⭐ Product Type End ⭐⭐ --}}

                                            {{-- ⭐⭐ Wholesale Pricing Tiers - Simple Clean Design ⭐⭐ --}}
                                            @if($details->is_wholesale && $details->wholesalePrices && $details->wholesalePrices->count() > 0)
                                            <div class="wholesale-pricing-section" style="margin: 20px 0;">
                                                <h5 style="margin-bottom: 15px; font-size: 16px; font-weight: 600; color: #333;">
                                                    <i class="fa fa-tag me-2"></i> Wholesale Pricing
                                                </h5>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover mb-0" style="background: #fff;">
                                                        <thead style="background: #f8f9fa;">
                                                            <tr>
                                                                <th style="padding: 12px; font-size: 14px; font-weight: 600;">Quantity</th>
                                                                <th style="padding: 12px; font-size: 14px; font-weight: 600;">Price</th>
                                                                <th style="padding: 12px; font-size: 14px; font-weight: 600;">Stock</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($details->wholesalePrices->sortBy('min_quantity') as $tier)
                                                            <tr class="wholesale-tier-row" 
                                                                data-min-qty="{{ $tier->min_quantity }}" 
                                                                data-max-qty="{{ $tier->max_quantity ?? 999999 }}" 
                                                                data-price="{{ $tier->wholesale_price }}"
                                                                style="cursor: pointer; transition: background 0.2s;">
                                                                <td style="padding: 12px; font-size: 14px;">
                                                                    {{ $tier->min_quantity }}{{ $tier->max_quantity ? ' - ' . $tier->max_quantity : '+' }} pcs
                                                                </td>
                                                                <td style="padding: 12px; font-size: 14px; font-weight: 600; color: #28a745;">
                                                                    ৳{{ number_format($tier->wholesale_price, 2) }}
                                                                </td>
                                                                <td style="padding: 12px; font-size: 14px; color: {{ ($tier->stock ?? 0) > 0 ? '#28a745' : '#dc3545' }};">
                                                                    {{ $tier->stock ?? 0 }} pcs
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p class="text-muted mt-2 mb-0" style="font-size: 12px;">
                                                    <i class="fa fa-info-circle me-1"></i> Quantity select করলে wholesale price automatically apply হবে
                                                </p>
                                            </div>
                                            @endif
                                            {{-- ⭐⭐ Wholesale Pricing End ⭐⭐ --}}

                                            <form action="{{ route('cart.store') }}" method="POST" name="formName" class="ajax-cart-form" id="productDetailsCartForm" onsubmit="return handleDetailsCartSubmit(event)">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $details->id }}" />


{{-- ✅ Variant-based Color & Size (with your old design style) --}}
@if ($details->variantPrices->count() > 0)
    @php
        $productcolors = $details->variantPrices->pluck('color')->unique('id')->filter();
        $productsizes = $details->variantPrices->pluck('size')->unique('id')->filter();
    @endphp

    {{-- 🎨 Color Section --}}
    @if ($productcolors->count() > 0)
        <div class="pro-color" style="width: 100%;">
            <div class="color_inner">
                <p>Color -</p>
                <div class="size-container">
                    <div class="selector">
                        @foreach ($productcolors as $procolor)
                            <div class="selector-item">
                                {{-- ✅ এখন color_id পাঠানো হচ্ছে (নাম নয়) --}}
                                <input type="radio"
                                    id="fc-option{{ $procolor->id }}"
                                    value="{{ $procolor->id }}"
                                    name="product_color"
                                    class="selector-item_radio emptyalert"
                                    {{ $loop->first ? 'checked' : '' }}
                                    required />
                                <label for="fc-option{{ $procolor->id }}"
                                    style="background-color: {{ $procolor->color ?? '#ccc' }}"
                                    class="selector-item_label">
                                    <span>
                                        <img src="{{ asset('public/frontEnd/images/check-icon.svg') }}" alt="Checked Icon" />
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- 📏 Size Section --}}
    @if ($productsizes->count() > 0)
        <div class="pro-size" style="width: 100%;">
            <div class="size_inner">
                <p>Size & Variant - <span class="attibute-name"></span></p>
                <div class="size-container">
                    <div class="selector">
                        @foreach ($productsizes as $prosize)
                            <div class="selector-item">
                                {{-- ✅ এখন size_id পাঠানো হচ্ছে --}}
                                <input type="radio"
                                    id="f-option{{ $prosize->id }}"
                                    value="{{ $prosize->id }}"
                                    name="product_size"
                                    class="selector-item_radio emptyalert"
                                    {{ $loop->first ? 'checked' : '' }}
                                    required />
                                <label for="f-option{{ $prosize->id }}" class="selector-item_label">
                                    {{ $prosize->sizeName ?? $prosize->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif





                                                        @if ($details->pro_unit)
                                                            <div class="pro_unig">
                                                                <label>Unit: {{ $details->pro_unit }}</label>
                                                                <input type="hidden" name="pro_unit"
                                                                    value="{{ $details->pro_unit }}" />
                                                            </div>
                                                        @endif
                                                        <div class="pro_brand">
                                                            <p>Brand :
                                                                {{ $details->brand ? $details->brand->name : 'N/A' }}
                                                            </p>
                                                        </div>

                                                        <div class="row">
                                                            <div class="qty-cart col-sm-12">
                                                                <div class="quantity">
                                                                    <span class="minus">-</span>
                                                                    <input type="text" name="qty"
                                                                        value="1" />
                                                                    <span class="plus">+</span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex single_product col-sm-12 gap-2">
                                                                 <button type="submit" class="btn add_cart_btn detail-add-cart-btn" onclick="return sendSuccess();" name="add_cart" value="add_cart">
                                                                     <i class="fa-solid fa-cart-shopping me-1"></i> কার্টে যোগ করুন
                                                                 </button>
                                                                 <button type="submit" class="btn order_now_btn order_now_btn_m detail-order-btn" onclick="return sendSuccess();" name="order_now" value="order_now">
                                                                     <i class="fa-solid fa-bolt me-1"></i> এখনই অর্ডার করুন
                                                                 </button>
                                                            </div>
                                                        </div>

                                                        {{-- Call Now Button --}}
                                                        <div class="mt-2">
                                                            <a class="detail-call-btn w-100"
                                                                href="tel:{{ $contact->hotline }}">
                                                                <i class="fas fa-phone-alt"></i>
                                                                <span>হটলাইন: {{ $contact->hotline }}</span>
                                                            </a>
                                                        </div>

                                                        {{-- WhatsApp Order Button --}}
                                                        <div class="mt-2">
                                                            <a class="detail-whatsapp-btn w-100"
                                                                href="https://api.whatsapp.com/send?phone={{ $contact->whatsapp }}&text={{ urlencode('হ্যালো, আমি এই পণ্যটি অর্ডার করতে চাই: ' . $details->name . ' (মূল্য: ৳' . $displayPrice . ') - ' . Request::url()) }}"
                                                                target="_blank" rel="noopener">
                                                                <i class="fab fa-whatsapp"></i>
                                                                <span>WhatsApp এ অর্ডার করুন</span>
                                                            </a>
                                                        </div>

                                                        <style>
                                                            /* ===== Eye-Catching Product Detail Action Buttons ===== */
                                                            .single_product {
                                                                display: flex;
                                                                gap: 10px;
                                                                margin-top: 10px;
                                                            }
                                                            .detail-add-cart-btn, .detail-order-btn {
                                                                font-weight: 700 !important;
                                                                font-size: 15px !important;
                                                                border-radius: 12px !important;
                                                                padding: 13px 16px !important;
                                                                border: none !important;
                                                                display: inline-flex !important;
                                                                align-items: center !important;
                                                                justify-content: center !important;
                                                                gap: 8px !important;
                                                                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                                                                flex: 1;
                                                                cursor: pointer;
                                                                text-decoration: none;
                                                                letter-spacing: 0.2px;
                                                            }
                                                            /* Order Now Button: High-converting Vivid Coral-Red with Glow */
                                                            .detail-order-btn {
                                                                background: linear-gradient(135deg, #ff334b, #ff5e36) !important;
                                                                color: #ffffff !important;
                                                                box-shadow: 0 6px 20px rgba(255, 51, 75, 0.42) !important;
                                                                position: relative;
                                                                overflow: hidden;
                                                            }
                                                            .detail-order-btn:hover {
                                                                background: linear-gradient(135deg, #ff1f3a, #ff4c20) !important;
                                                                transform: translateY(-2px) scale(1.01) !important;
                                                                box-shadow: 0 8px 25px rgba(255, 51, 75, 0.6) !important;
                                                                color: #ffffff !important;
                                                            }
                                                            .detail-order-btn:active {
                                                                transform: translateY(0) scale(0.98) !important;
                                                            }

                                                            /* Add To Cart Button: Deep Indigo/Blue */
                                                            .detail-add-cart-btn {
                                                                background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
                                                                color: #ffffff !important;
                                                                box-shadow: 0 6px 20px rgba(79, 70, 229, 0.38) !important;
                                                            }
                                                            .detail-add-cart-btn:hover {
                                                                background: linear-gradient(135deg, #4338ca, #4f46e5) !important;
                                                                transform: translateY(-2px) scale(1.01) !important;
                                                                box-shadow: 0 8px 25px rgba(79, 70, 229, 0.55) !important;
                                                                color: #ffffff !important;
                                                            }
                                                            .detail-add-cart-btn:active {
                                                                transform: translateY(0) scale(0.98) !important;
                                                            }

                                                            /* Call Button: Clean Emerald Gradient */
                                                            .detail-call-btn {
                                                                display: flex;
                                                                align-items: center;
                                                                justify-content: center;
                                                                gap: 10px;
                                                                background: linear-gradient(135deg, #059669, #10b981);
                                                                color: #ffffff !important;
                                                                font-size: 16px;
                                                                font-weight: 700;
                                                                padding: 13px 20px;
                                                                border-radius: 12px;
                                                                text-decoration: none;
                                                                box-shadow: 0 4px 16px rgba(5, 150, 105, 0.32);
                                                                transition: all 0.3s ease;
                                                            }
                                                            .detail-call-btn:hover {
                                                                transform: translateY(-2px);
                                                                box-shadow: 0 8px 22px rgba(5, 150, 105, 0.48);
                                                                color: #ffffff;
                                                            }
                                                            .detail-call-btn:hover i {
                                                                animation: phoneShake 0.5s ease-in-out;
                                                            }

                                                            /* WhatsApp Order Button: Official WhatsApp Gradient with Soft Pulse */
                                                            .detail-whatsapp-btn {
                                                                display: flex;
                                                                align-items: center;
                                                                justify-content: center;
                                                                gap: 10px;
                                                                background: linear-gradient(135deg, #25D366, #128C7E);
                                                                color: #ffffff !important;
                                                                font-size: 16.5px;
                                                                font-weight: 700;
                                                                padding: 13px 20px;
                                                                border-radius: 12px;
                                                                text-decoration: none;
                                                                box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
                                                                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                                                animation: waPulse 2.5s infinite;
                                                            }
                                                            .detail-whatsapp-btn i {
                                                                font-size: 22px;
                                                            }
                                                            .detail-whatsapp-btn:hover {
                                                                transform: translateY(-2px) scale(1.01);
                                                                box-shadow: 0 8px 26px rgba(37, 211, 102, 0.65);
                                                                color: #ffffff;
                                                                animation: none;
                                                            }
                                                            .detail-whatsapp-btn:active {
                                                                transform: translateY(0) scale(0.98);
                                                            }

                                                            @keyframes waPulse {
                                                                0%, 100% {
                                                                    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
                                                                }
                                                                50% {
                                                                    box-shadow: 0 6px 28px rgba(37, 211, 102, 0.7);
                                                                }
                                                            }
                                                            @keyframes phoneShake {
                                                                0%, 100% { transform: rotate(0); }
                                                                20%, 60% { transform: rotate(-15deg); }
                                                                40%, 80% { transform: rotate(15deg); }
                                                            }

                                                            /* Mobile Tweaks */
                                                            @media (max-width: 576px) {
                                                                .detail-add-cart-btn, .detail-order-btn {
                                                                    font-size: 13.5px !important;
                                                                    padding: 11px 8px !important;
                                                                    border-radius: 10px !important;
                                                                }
                                                                .detail-whatsapp-btn, .detail-call-btn {
                                                                    font-size: 14.5px !important;
                                                                    padding: 11px 16px !important;
                                                                    border-radius: 10px !important;
                                                                }
                                                                .detail-whatsapp-btn i {
                                                                    font-size: 19px !important;
                                                                }
                                                            }
                                                        </style>

                                                        <div class="mt-md-2 mt-2">
                                                            <div class="del_charge_area">
                                                                <div class="alert alert-info text-xs">
                                                                    <div class="flext_area">
                                                                        <i class="fa-solid fa-cubes"></i>
                                                                        <div>

                                                                            @foreach ($shippingcharge as $key => $value)
                                                                                <span>{{ $value->name }} <br /></span>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                     
                                            </form>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<div class="description-nav-wrapper">
    <div class="container">
        <div class="row">

            <div class="col-sm-12">
                <div class="description-nav">
                    <ul class="desc-nav-ul">
                        {{-- <li class="active">
                            <a href="#specification" target="_self">Specification</a>
                        </li> --}}
                        <li>
                            <a href="#description" target="_self">Description</a>
                        </li>
                        {{-- <li>
                            <a href="#question" target="_self">Questions (0)</a>
                        </li> --}}
                        <li>
                            <a href="#writeReview" target="_self">Reviews ({{ $reviews->count() }}) </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="pro_details_area">
    <div class="container">
        <div class="row">
            <div class="col-sm-8">
                <div class="description tab-content details-action-box" id="description">
                    <h2>বিস্তারিত</h2>
                    <p>{!! $details->description !!}</p>
                </div>
                <div class="tab-content details-action-box" id="writeReview">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                
							  
							  
							  
							<section class="gomobd-review-section mt-5" id="reviewsList">
    <div class="gomobd-review-header d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h3 class="gomobd-review-title fw-bold mb-2 mb-md-0">
            Customer Reviews ({{ $reviews->count() }})
        </h3>
        <button type="button" class="gomobd-review-btn btn btn-success btn-sm"
            data-bs-toggle="modal" data-bs-target="#exampleModal">
            <i class="fa fa-edit me-1"></i> Write a Review
        </button>
    </div>

    @if ($reviews->count() > 0)
    <div class="gomobd-review-list row g-3">
        @foreach ($reviews as $review)
        <div class="col-12">
            <div class="gomobd-review-card shadow-sm">
                <div class="gomobd-review-card-header d-flex justify-content-between align-items-start flex-wrap">
                    <div class="d-flex align-items-center">
                        <div class="gomobd-review-avatar">
                            {{ strtoupper(substr($review->name, 0, 1)) }}
                        </div>
                        <div class="gomobd-review-meta">
                            <h6 class="gomobd-review-name">{{ $review->name }}</h6>
                            <small class="gomobd-review-date">{{ $review->created_at->format('d M Y') }}</small>
                        </div>
                    </div>
                    <div class="gomobd-review-stars">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $review->ratting)
                                <i class="fa-solid fa-star"></i>
                            @else
                                <i class="fa-regular fa-star"></i>
                            @endif
                        @endfor
                    </div>
                </div>
                <div class="gomobd-review-body mt-2">
                    <p><i class="fa-regular fa-comment-dots text-success me-1"></i> {{ $review->review }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="gomobd-review-empty text-center py-5">
        <i class="fa fa-clipboard-list fs-1 text-muted mb-3"></i>
        <p>This product has no reviews yet.<br><strong>Be the first one to write a review.</strong></p>
    </div>
    @endif
</section>

                            </div>
                        </div>
                    </div>
                </div>
            @php
                $videoType = $details->pro_video_type ?? ($details->pro_video ? 'youtube' : null);
                $hasVideo = ($videoType === 'youtube' && $details->pro_video) || ($videoType === 'upload' && $details->pro_video_path);
            @endphp
            @if($hasVideo)
            <div class="col-sm-4">
                <div class="pro_vide">
                    <h2>ভিডিও</h2>
                    @if($videoType === 'youtube' && $details->pro_video)
                    <iframe width="100%" height="315"
                        src="https://www.youtube.com/embed/{{ $details->pro_video }}" title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>
                    @elseif($videoType === 'upload' && $details->pro_video_path)
                    <video width="100%" height="315" controls style="border-radius:8px;background:#000;">
                        <source src="{{ asset($details->pro_video_path) }}" type="video/mp4">
                        <source src="{{ asset($details->pro_video_path) }}" type="video/webm">
                        <source src="{{ asset($details->pro_video_path) }}" type="video/ogg">
                        আপনার ব্রাউজার ভিডিও সাপোর্ট করে না।
                    </video>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

<section class="related-product-section">
    <div class="container">
        <div class="row">
            <div class="related-title">
                <h5>Related Product</h5>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="product-inner owl-carousel related_slider">
                    @foreach ($products as $key => $value)
                    <div class="product_item wist_item wow zoomIn" data-wow-duration="1.5s"
                        data-wow-delay="0.{{ $key }}s">

                        <div class="product_item_inner">
                            @if($value->old_price)
                            <div class="sale-badge">
                                <div class="sale-badge-inner">
                                    <div class="sale-badge-box">
                                        <span class="sale-badge-text">
                                            <p>@php $discount=(((($value->old_price)-($value->new_price))*100) / ($value->old_price)) @endphp 
                                               {{ number_format($discount, 0) }}%</p>
                                            ছাড়
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="pro_img">
                                <a href="{{ route('product', $value->slug) }}">
                                    <img src="{{ asset($value->image ? $value->image->image : '') }}"
                                        alt="{{ $value->name }}" />
                                </a>
                            </div>

                            <div class="pro_des">
                                <div class="pro_name">
                                    <a href="{{ route('product', $value->slug) }}">{{ Str::limit($value->name, 35) }}</a>
                                </div>
                            </div>
                        </div>

                        @php
                            $averageRating = $value->reviews->avg('ratting'); 
                            $filledStars = floor($averageRating);
                            $hasHalfStar = $averageRating - $filledStars >= 0.5;
                            $emptyStars = 5 - $filledStars - ($hasHalfStar ? 1 : 0);
                        @endphp

                        {{-- Stars --}}
                        @for ($i = 0; $i < $filledStars; $i++)
                            <i class="fas fa-star"></i>
                        @endfor
                        @if ($hasHalfStar)
                            <i class="fas fa-star-half-alt"></i>
                        @endif
                        @for ($i = 0; $i < $emptyStars; $i++)
                            <i class="far fa-star"></i>
                        @endfor

                        @php
                            $cardPrice = ($value->variantPrices && $value->variantPrices->isNotEmpty() && $value->variantPrices->first()->price > 0)
                                ? $value->variantPrices->first()->price 
                                : $value->new_price;
                            $hasVariants = ($value->variantPrices && $value->variantPrices->isNotEmpty()) 
                                || ($value->prosizes && $value->prosizes->isNotEmpty()) 
                                || ($value->procolors && $value->procolors->isNotEmpty());
                        @endphp
                        <div class="pro_price">
                            <p>
                                @if($value->old_price && $value->old_price > $cardPrice)
                                    <del>৳ {{ $value->old_price }}</del>
                                @endif
                                ৳ {{ $cardPrice }}
                            </p>
                        </div>

                        {{-- বাটন লজিক: স্টক আউট / ভ্যারিয়েন্ট মডাল / ডাইরেক্ট কার্ট --}}
                        @if (!is_null($value->stock) && $value->stock < 1)
                            <div class="pro_btn">
                                <a href="{{ route('product', $value->slug) }}" class="order-btn-link order-btn">
                                    স্টক আউট
                                </a>
                                <a href="{{ route('product', $value->slug) }}" class="cart-icon-link cart-icon-btn">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </a>
                            </div>
                        @elseif ($hasVariants)
                            <div class="pro_btn">
                                <button type="button" class="order-btn open-variant-modal" data-id="{{ $value->id }}" data-action="order_now">
                                    অর্ডার করুন
                                </button>
                                <button type="button" class="cart-icon-btn open-variant-modal" data-id="{{ $value->id }}" data-action="add_cart">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </button>
                            </div>
                        @else
                            {{-- ভ্যারিয়েন্ট নেই = Order Now + Add to Cart --}}
                            <div class="pro_btn">
                                {{-- Order Now --}}
                                <form action="{{ route('cart.store') }}" method="POST" class="ajax-cart-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $value->id }}">
                                    <input type="hidden" name="qty" value="1">
                                    <input type="hidden" name="order_now" value="1">

                                    <button type="submit" class="order-btn">
                                        অর্ডার করুন
                                    </button>
                                </form>

                                {{-- Add to Cart --}}
                                <form action="{{ route('cart.store') }}" method="POST" class="ajax-cart-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $value->id }}">
                                    <input type="hidden" name="qty" value="1">

                                    <button type="submit" class="cart-icon-btn">
                                        <i class="fa-solid fa-cart-shopping"></i>
                                    </button>
                                </form>
                            </div>
                        @endif

                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Review Modal: body এর কাছাকাছি রাখা হয়েছে যাতে z-index/stacking issue না হয় --}}
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Your review</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="insert-review">
                    @if (Auth::guard('customer')->user())
                        <form action="{{ route('customer.review') }}" id="review-form" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $details->id }}">
                            <div class="fz-12 mb-2">
                                <div class="rating">
                                    <label title="Excelent">☆ <input required type="radio" name="ratting" value="5" /></label>
                                    <label title="Best">☆ <input required type="radio" name="ratting" value="4" /></label>
                                    <label title="Better">☆ <input required type="radio" name="ratting" value="3" /></label>
                                    <label title="Very Good">☆ <input required type="radio" name="ratting" value="2" /></label>
                                    <label title="Good">☆ <input required type="radio" name="ratting" value="1" /></label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="col-form-label">Message:</label>
                                <textarea required class="form-control radius-lg" name="review" id="message-text"></textarea>
                                <span id="validation-message" style="color: red;"></span>
                            </div>
                            <div class="form-group">
                                <button class="details-review-button" type="submit">Submit Review</button>
                            </div>
                        </form>
                    @else
                        <a class="customer-login-redirect" href="{{ route('customer.login') }}">Login to Post Your Review</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection @push('script')
<script src="{{ asset('public/frontEnd/js/owl.carousel.min.js') }}"></script>

<script src="{{ asset('public/frontEnd/js/zoomsl.min.js') }}"></script>
<script>
    // Review modal কে body তে সরিয়ে দেওয়া - #content এর z-index:1 এর জন্য modal backdrop এর পিছনে পড়ছিল
    document.addEventListener('DOMContentLoaded', function() {
        var m = document.getElementById('exampleModal');
        if (m && document.body) document.body.appendChild(m);
    });
</script>
<script>
    const variants = @json($details->variantPrices);

    @if($details->is_wholesale && $details->wholesalePrices && $details->wholesalePrices->count() > 0)
    var wholesaleTiers = [
        @foreach($details->wholesalePrices->sortBy('min_quantity') as $tier)
        {
            min_quantity: {{ $tier->min_quantity }},
            max_quantity: {{ $tier->max_quantity ?? 999999 }},
            price: {{ $tier->wholesale_price }}
        }@if(!$loop->last),@endif
        @endforeach
    ];
    var regularPrice = {{ $details->new_price }};

    function getWholesalePrice(qty) {
        for (var i = 0; i < wholesaleTiers.length; i++) {
            if (qty >= wholesaleTiers[i].min_quantity && qty <= wholesaleTiers[i].max_quantity) {
                return wholesaleTiers[i].price;
            }
        }
        return null;
    }
    @endif

    function updateVariantPrice() {
        let colorInput = $("input[name='product_color']:checked");
        let sizeInput  = $("input[name='product_size']:checked");

        let color = colorInput.val() || null;
        let size  = sizeInput.val() || null;

        // Update selected attribute name display
        let selectedNames = [];
        if (sizeInput.length) {
            let sLabel = sizeInput.closest('.selector-item').find('label').text().trim();
            if (sLabel) selectedNames.push(sLabel);
        }
        if (colorInput.length) {
            let cLabel = colorInput.closest('.selector-item').find('label').attr('title') || colorInput.closest('.selector-item').find('label').text().trim();
            if (cLabel) selectedNames.push(cLabel);
        }
        $('.attibute-name').text(selectedNames.length ? ': ' + selectedNames.join(' / ') : '');

        let match = null;

        // 1. Both color and size selected
        if (color && size) {
            match = variants.find(v => {
                let vColorId = (v.color_id !== null && v.color_id !== undefined) ? String(v.color_id) : (v.color && v.color.id ? String(v.color.id) : null);
                let vSizeId  = (v.size_id  !== null && v.size_id  !== undefined) ? String(v.size_id)  : (v.size && v.size.id ? String(v.size.id) : null);
                return vColorId === String(color) && vSizeId === String(size);
            });
        }

        // 2. Only size selected (prefer variant with null/empty color, then fallback to any matching size)
        if (!match && size) {
            match = variants.find(v => {
                let vColorId = (v.color_id !== null && v.color_id !== undefined) ? String(v.color_id) : (v.color && v.color.id ? String(v.color.id) : null);
                let vSizeId  = (v.size_id  !== null && v.size_id  !== undefined) ? String(v.size_id)  : (v.size && v.size.id ? String(v.size.id) : null);
                return vSizeId === String(size) && (!vColorId || vColorId === '');
            });
            if (!match) {
                match = variants.find(v => {
                    let vSizeId = (v.size_id !== null && v.size_id !== undefined) ? String(v.size_id) : (v.size && v.size.id ? String(v.size.id) : null);
                    return vSizeId === String(size);
                });
            }
        }

        // 3. Only color selected (prefer variant with null/empty size, then fallback to any matching color)
        if (!match && color) {
            match = variants.find(v => {
                let vColorId = (v.color_id !== null && v.color_id !== undefined) ? String(v.color_id) : (v.color && v.color.id ? String(v.color.id) : null);
                let vSizeId  = (v.size_id  !== null && v.size_id  !== undefined) ? String(v.size_id)  : (v.size && v.size.id ? String(v.size.id) : null);
                return vColorId === String(color) && (!vSizeId || vSizeId === '');
            });
            if (!match) {
                match = variants.find(v => {
                    let vColorId = (v.color_id !== null && v.color_id !== undefined) ? String(v.color_id) : (v.color && v.color.id ? String(v.color.id) : null);
                    return vColorId === String(color);
                });
            }
        }

        // Fallback: first variant if none matched
        if (!match && variants && variants.length > 0) {
            match = variants[0];
        }

        let basePrice = parseFloat({{ (float)$displayPrice }});
        if (match && match.price !== undefined && match.price !== null && parseFloat(match.price) > 0) {
            basePrice = parseFloat(match.price);
        }

        // Apply wholesale price if applicable (wholesale price overrides variant price)
        @if($details->is_wholesale && $details->wholesalePrices && $details->wholesalePrices->count() > 0)
        let qty = parseInt($("input[name='qty']").val()) || 1;
        let wholesalePrice = getWholesalePrice(qty);
        if (wholesalePrice !== null) {
            basePrice = parseFloat(wholesalePrice);
        }
        @endif

        $('#newPrice').text('৳' + basePrice.toFixed(2));

        // Update Old Price display
        let oldPriceVal = parseFloat({{ (float)($details->old_price ?? 0) }});
        if (oldPriceVal > basePrice) {
            $('#oldPrice').show().text('৳' + oldPriceVal.toFixed(2));
        } else {
            $('#oldPrice').hide();
        }
    }

    // ভ্যারিয়েন্ট ইমেজ লিস্ট (কালার ও সাইজ উভয়টি অন্তর্ভুক্ত)
    @php
        $productImagesList = [];
        if ($details->images) {
            foreach ($details->images as $img) {
                $productImagesList[] = [
                    'src'      => asset($img->image),
                    'color_id' => !empty($img->color_id) ? (string)$img->color_id : null,
                    'size_id'  => !empty($img->size_id) ? (string)$img->size_id : null,
                ];
            }
        }
    @endphp
    var productImages = {!! json_encode($productImagesList) !!};
    var currentVariantImgKey = null;

    function initDetailsSliders() {
        var $slider = $(".details_slider");
        if (!$slider.hasClass("owl-loaded")) {
            $slider.owlCarousel({
                margin: 15,
                items: 1,
                loop: $slider.find(".dimage_item").length > 1,
                dots: false,
                autoplay: false,
                nav: false,
            });

            // স্লাইড পরিবর্তন হলে থাম্বনেইল একটিভ ক্লাস সিঙ্ক
            $slider.on("changed.owl.carousel", function(event) {
                if (!event.item || event.item.count <= 0) return;
                var count = event.item.count;
                var currentIdx = event.item.index;
                var cloneOffset = event.relatedTarget && event.relatedTarget._clones ? event.relatedTarget._clones.length / 2 : 0;
                var realIdx = (currentIdx - cloneOffset) % count;
                if (realIdx < 0) realIdx += count;
                $(".indicator-item").removeClass("active");
                $(".indicator-item[data-id='" + realIdx + "']").addClass("active");
            });
        }

        var $thumbWrapper = $("#indicator_thumb_wrapper");
        if (!$thumbWrapper.hasClass("owl-loaded") && $thumbWrapper.find(".indicator-item").length > 4) {
            $thumbWrapper.addClass("thumb_slider owl-carousel");
            $thumbWrapper.owlCarousel({
                margin: 10,
                items: 4,
                loop: false,
                dots: false,
                nav: true,
                autoplay: false
            });
        }

        if ($(".block__pic").length && typeof $(".block__pic").imagezoomsl === "function") {
            try { $(".block__pic").imagezoomsl({ zoomrange: [3, 3] }); } catch (e) {}
        }
    }

    // থাম্বনেইলে ক্লিক করলে মেইন স্লাইডার সেই ইমেজে যাবে এবং ভ্যারিয়েন্ট সিলেক্ট হবে
    $(document).on("click", ".indicator-item", function() {
        var slideIndex = parseInt($(this).data("id"), 10);
        var $slider = $(".details_slider");
        if ($slider.hasClass("owl-loaded")) {
            $slider.trigger("to.owl.carousel", [slideIndex, 250]);
        }
        $(".indicator-item").removeClass("active");
        $(this).addClass("active");

        // যদি এই থাম্বনেইলের সাথে সাইজ বা কালার যুক্ত থাকে, তাহলে রেডিও বাটন অটো সিলেক্ট হবে
        var sId = $(this).data("size-id");
        var cId = $(this).data("color-id");
        if (sId) {
            var $sRadio = $("input[name='product_size'][value='" + sId + "']");
            if ($sRadio.length && !$sRadio.is(":checked")) {
                $sRadio.prop("checked", true).trigger("change");
            }
        }
        if (cId) {
            var $cRadio = $("input[name='product_color'][value='" + cId + "']");
            if ($cRadio.length && !$cRadio.is(":checked")) {
                $cRadio.prop("checked", true).trigger("change");
            }
        }
    });

    // ভ্যারিয়েন্ট (কালার/সাইজ) সিলেক্ট করলে মেইন স্লাইডার সেই ভ্যারিয়েন্টের ইমেজে যাবে (থাম্বনেইল নষ্ট হবে না)
    function syncVariantImage(colorId, sizeId) {
        if (!productImages || productImages.length <= 1) return;
        var colorIdStr = colorId ? String(colorId) : '';
        var sizeIdStr  = sizeId ? String(sizeId) : '';

        var targetIndex = -1;

        // ১. কালার এবং সাইজ উভয় মিলে
        if (colorIdStr && sizeIdStr) {
            targetIndex = productImages.findIndex(function(img) {
                return String(img.color_id) === colorIdStr && String(img.size_id) === sizeIdStr;
            });
        }
        // ২. শুধুমাত্র সাইজ মিলে
        if (targetIndex === -1 && sizeIdStr) {
            targetIndex = productImages.findIndex(function(img) {
                return String(img.size_id) === sizeIdStr;
            });
        }
        // ৩. শুধুমাত্র কালার মিলে
        if (targetIndex === -1 && colorIdStr) {
            targetIndex = productImages.findIndex(function(img) {
                return String(img.color_id) === colorIdStr;
            });
        }

        if (targetIndex !== -1) {
            var $slider = $(".details_slider");
            if ($slider.hasClass("owl-loaded")) {
                $slider.trigger("to.owl.carousel", [targetIndex, 250]);
            }
            $(".indicator-item").removeClass("active");
            $(".indicator-item[data-id='" + targetIndex + "']").addClass("active");
        }
    }

    $(document).ready(function() {
        initDetailsSliders();
        updateVariantPrice();
        var initialColor = $("input[name='product_color']:checked").val() || null;
        var initialSize  = $("input[name='product_size']:checked").val() || null;
        if (initialColor || initialSize) {
            syncVariantImage(initialColor, initialSize);
        }
    });

    $(document).on(
        'change',
        "input[name='product_color'], input[name='product_size']",
        function() {
            updateVariantPrice();
            var c = $("input[name='product_color']:checked").val() || null;
            var s = $("input[name='product_size']:checked").val() || null;
            syncVariantImage(c, s);
        }
    );
</script>


<script>
    $(document).ready(function() {
        $(".related_slider").owlCarousel({
            margin: 10,
            items: 6,
            loop: true,
            dots: true,
            nav: true,
            autoplay: true,
            autoplayTimeout: 6000,
            autoplayHoverPause: true,
            responsiveClass: true,
            responsive: {
                0: {
                    items: 2,
                    nav: true,
                },
                600: {
                    items: 3,
                    nav: false,
                },
                1000: {
                    items: 5,
                    nav: true,
                    loop: true,
                },
            },
        });
        // $('.owl-nav').remove();
    });
</script>
<script>
    $(document).ready(function() {
        $(".minus").click(function() {
            var $input = $(this).parent().find("input");
            var count = parseInt($input.val()) - 1;
            count = count < 1 ? 1 : count;
            $input.val(count);
            $input.change();
            return false;
        });
        $(".plus").click(function() {
            var $input = $(this).parent().find("input");
            $input.val(parseInt($input.val()) + 1);
            $input.change();
            return false;
        });

        // Wholesale Price Update on Quantity Change - Modern Card Design
        @if($details->is_wholesale && $details->wholesalePrices && $details->wholesalePrices->count() > 0)
        var wholesaleTiers = [
            @foreach($details->wholesalePrices->sortBy('min_quantity') as $tier)
            {
                min_quantity: {{ $tier->min_quantity }},
                max_quantity: {{ $tier->max_quantity ?? 999999 }},
                price: {{ $tier->wholesale_price }}
            }@if(!$loop->last),@endif
            @endforeach
        ];
        var regularPrice = {{ $details->new_price }};

        function updatePriceBasedOnQuantity() {
            var qty = parseInt($("input[name='qty']").val()) || 1;
            var selectedPrice = regularPrice;
            var matchedTier = null;

            // Find matching wholesale tier
            for (var i = 0; i < wholesaleTiers.length; i++) {
                if (qty >= wholesaleTiers[i].min_quantity && qty <= wholesaleTiers[i].max_quantity) {
                    selectedPrice = wholesaleTiers[i].price;
                    matchedTier = wholesaleTiers[i];
                    break;
                }
            }

            // Update price display
            $('#newPrice').text('৳' + selectedPrice.toFixed(2));

            // Highlight matching tier row
            $('.wholesale-tier-row').removeClass('active-tier');
            if (matchedTier) {
                $('.wholesale-tier-row').each(function() {
                    var minQty = parseInt($(this).data('min-qty'));
                    var maxQty = parseInt($(this).data('max-qty'));
                    if (qty >= minQty && qty <= maxQty) {
                        $(this).addClass('active-tier');
                    }
                });
            }
        }

        // Update price when quantity changes
        $("input[name='qty']").on('change keyup', function() {
            updatePriceBasedOnQuantity();
        });

        // Click on tier row to set quantity to minimum
        $('.wholesale-tier-row').on('click', function() {
            var minQty = parseInt($(this).data('min-qty'));
            $("input[name='qty']").val(minQty).trigger('change');
        });

        // Initial price update
        updatePriceBasedOnQuantity();
        @endif
    });
</script>

<script>
    function sendSuccess() {
        var form = document.forms["formName"];
        if (!form) return true;
        var sizeEl = form["product_size"];
        var colorEl = form["product_color"];
        if (sizeEl) {
            var size = sizeEl.value || "";
            if (size === "") {
                toastr.warning("Please select any size");
                return false;
            }
        }
        if (colorEl) {
            var color = colorEl.value || "";
            if (color === "") {
                toastr.error("Please select any color");
                return false;
            }
        }
        return true;
    }
    function handleDetailsCartSubmit(event) {
        if (!sendSuccess()) return false;
        event.preventDefault();
        // master.blade এ document-level submit লিসেনার আছে; বাবল হলে একই ফর্মে দ্বিতীয়বার POST হয়
        if (event.stopPropagation) event.stopPropagation();
        if (event.stopImmediatePropagation) event.stopImmediatePropagation();
        var form = document.getElementById("productDetailsCartForm");
        if (!form || form.classList.contains("cart-ajax-submit")) return false;
        var isOrderNow = event.submitter && event.submitter.name === "order_now";
        form.classList.add("cart-ajax-submit");
        var formData = new FormData(form);
        if (isOrderNow) formData.append("order_now", "1");
        var qtyInput = form.querySelector("input[name='qty']");
        var qty = parseInt(qtyInput ? qtyInput.value : "1", 10) || 1;
        var atcEventId = 'atc_' + '{{ $details->id }}' + '_' + Math.floor(Date.now() / 1000);
        formData.append("event_id", atcEventId);

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "{{ route('cart.store') }}");
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("X-CSRF-TOKEN", document.querySelector('meta[name="csrf-token"]').content);
        xhr.setRequestHeader("Accept", "application/json");
        xhr.onload = function() {
            form.classList.remove("cart-ajax-submit");
            try {
                var data = JSON.parse(xhr.responseText);
                if (data && data.success) {
                    if (typeof fireAddToCartEvent === "function") {
                        // ⭐ DB price ব্যবহার (server response priority) — DOM/Blade hardcode বাদ
                        var serverPrice = (data && data.price) ? parseFloat(data.price) : {{ (float)($details->new_price ?? 0) }};
                        var serverQty   = (data && data.qty)   ? parseInt(data.qty)     : qty;
                        fireAddToCartEvent(null, '{{ $details->id }}', serverQty, @json($details->name), serverPrice, @json(optional($details->category)->name ?? ''), (data && data.event_id) ? data.event_id : atcEventId);
                    }
                    if (typeof toastr !== "undefined") toastr.success("কার্টে যোগ হয়েছে!", "সফল");
                    if (typeof cart_count === "function") cart_count();
                    if (typeof mobile_cart === "function") mobile_cart();
                    if (typeof sidebarCartRefresh === "function") sidebarCartRefresh();
                    if (data.redirect || isOrderNow) {
                        window.location.href = data.redirect || "{{ route('customer.checkout') }}";
                    } else {
                        if (typeof runFlyToCart === "function") runFlyToCart($(form), function() {
                            if (typeof openSidebarCart === "function") openSidebarCart();
                        });
                        else if (typeof openSidebarCart === "function") openSidebarCart();
                    }
                } else {
                    form.submit();
                }
            } catch (e) {
                form.submit();
            }
        };
        xhr.onerror = function() {
            form.classList.remove("cart-ajax-submit");
            form.submit();
        };
        var params = new URLSearchParams(formData).toString();
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send(params);
        return false;
    }
</script>
<script>
    $(document).ready(function() {
        $(".rating label").click(function() {
            $(".rating label").removeClass("active");
            $(this).addClass("active");
        });
    });
</script>

@endpush
