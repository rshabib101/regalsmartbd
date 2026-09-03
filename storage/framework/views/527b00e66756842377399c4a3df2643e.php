<?php $__env->startSection('title', $details->name); ?>
<?php
    $pageType = 'product';
    $firstVariant = ($details->variantPrices && $details->variantPrices->count() > 0) ? $details->variantPrices->first() : null;
    $displayPrice = ($firstVariant && $firstVariant->price > 0) ? $firstVariant->price : ($details->new_price ?? 0);
    $firstVariantUnit = optional($firstVariant?->size)->sizeName ?? ($details->pro_unit ?? '');
?>
<?php $__env->startPush('dataLayer'); ?>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({ ecommerce: null });
window.dataLayer.push({
    event: 'view_item',
    ecommerce: {
        currency: 'BDT',
        value: <?php echo e((float) $displayPrice); ?>,
        items: [{
            item_id: '<?php echo e($details->id); ?>',
            item_name: <?php echo json_encode($details->name, 15, 512) ?>,
            price: <?php echo e((float) $displayPrice); ?>,
            item_brand: <?php echo json_encode(optional($details->brand)->name ?? ($generalsetting->name ?? ''), 15, 512) ?>,
            item_category: <?php echo json_encode(optional($details->category)->name ?? '', 15, 512) ?>,
            item_variant: <?php echo json_encode($firstVariantUnit, 15, 512) ?>,
            quantity: 1
        }]
    }
});

<?php if(isset($products) && $products->count() > 0): ?>
window.dataLayer.push({
    event: 'view_item_list',
    ecommerce: {
        item_list_name: 'Related Products',
        currency: 'BDT',
        items: [
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            {
                item_id: '<?php echo e($rp->id); ?>',
                item_name: <?php echo json_encode($rp->name, 15, 512) ?>,
                price: <?php echo e((float) ($rp->new_price ?? 0)); ?>,
                item_brand: <?php echo json_encode(optional($rp->brand)->name ?? '', 15, 512) ?>,
                item_category: <?php echo json_encode(optional($rp->category)->name ?? '', 15, 512) ?>,
                index: <?php echo e($loop->iteration); ?>

            }<?php if(!$loop->last): ?>,<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        ]
    }
});
<?php endif; ?>

if (typeof fbq === 'function') {
    fbq('track', 'ViewContent', {
        content_ids:      ['<?php echo e($details->id); ?>'],
        content_name:     <?php echo json_encode($details->name, 15, 512) ?>,
        content_category: <?php echo json_encode(optional($details->category)->name ?? '', 15, 512) ?>,
        content_type:     'product',
        value:            <?php echo e((float) $displayPrice); ?>,
        currency:         'BDT',
        // ⭐ Bhoorwear standard: contents, shipping, num_items
        contents:  [{ id: '<?php echo e($details->id); ?>', quantity: 1, item_price: <?php echo e((float) $displayPrice); ?> }],
        shipping:  0,
        num_items: 1
    }, { eventID: '<?php echo e($vc_event_id ?? ("vc_prod_".$details->id."_".time())); ?>' });
}
if (typeof ttq !== 'undefined') {
    ttq.track('ViewContent', {
        content_type: 'product',
        content_id: '<?php echo e($details->id); ?>',
        content_name: <?php echo json_encode($details->name, 15, 512) ?>,
        value: <?php echo e((float) $displayPrice); ?>,
        currency: 'BDT'
    });
}
<?php $__env->stopPush(); ?>
<?php $__env->startPush('seo'); ?>
<?php
    $metaTitle = $details->meta_title ?? $details->name;
    $metaDescription = $details->meta_description ?? Str::limit(strip_tags($details->description), 160);
    $metaKeywords = $details->meta_keywords ?? $details->name;
    $metaImage = $details->meta_image ? asset($details->meta_image) : asset(optional($details->image)->image);
    $brandName = optional($details->brand)->name ?? ($generalsetting->name ?? 'Smart Grammo Bazar');
    $categoryName = optional($details->category)->name ?? 'General';
    $inStock = (!is_null($details->stock) && $details->stock < 1) ? false : true;
?>

<meta name="app-url" content="<?php echo e(route('product', $details->slug)); ?>" />
<meta name="robots" content="index, follow" />

<meta name="title" content="<?php echo e($metaTitle); ?>" />
<meta name="description" content="<?php echo e($metaDescription); ?>" />
<meta name="keywords" content="<?php echo e($metaKeywords); ?>" />

<!-- Twitter Card data -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:site" content="@gomobd" />
<meta name="twitter:title" content="<?php echo e($metaTitle); ?>" />
<meta name="twitter:description" content="<?php echo e($metaDescription); ?>" />
<meta name="twitter:image" content="<?php echo e($metaImage); ?>" />

<!-- Open Graph data -->
<meta property="og:title" content="<?php echo e($metaTitle); ?>" />
<meta property="og:type" content="product" />
<meta property="og:url" content="<?php echo e(route('product', $details->slug)); ?>" />
<meta property="og:image" content="<?php echo e($metaImage); ?>" />
<meta property="og:description" content="<?php echo e($metaDescription); ?>" />
<meta property="og:site_name" content="<?php echo e($generalsetting->name ?? 'Smart Grammo Bazar'); ?>" />

<!-- Facebook / Meta Product Catalog Microdata -->
<meta property="product:retailer_item_id" content="<?php echo e($details->id); ?>" />
<meta property="product:price:amount" content="<?php echo e($displayPrice); ?>" />
<meta property="product:price:currency" content="BDT" />
<?php if($details->old_price && $details->old_price > $displayPrice): ?>
<meta property="product:sale_price:amount" content="<?php echo e($displayPrice); ?>" />
<meta property="product:sale_price:currency" content="BDT" />
<meta property="product:original_price:amount" content="<?php echo e($details->old_price); ?>" />
<?php endif; ?>
<meta property="product:availability" content="<?php echo e($inStock ? 'in stock' : 'out of stock'); ?>" />
<meta property="product:condition" content="new" />
<meta property="product:brand" content="<?php echo e($brandName); ?>" />
<meta property="product:category" content="<?php echo e($categoryName); ?>" />

<?php
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
?>

<!-- Schema.org JSON-LD Structured Data (Google & Meta Merchant standard) -->
<script type="application/ld+json">
<?php echo json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>

</script>
<?php $__env->stopPush(); ?>


<?php $__env->startPush('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('public/frontEnd/css/zoomsl.css')); ?>">
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="homeproduct main-details-page">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <section class="product-section">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-6 position-relative">
                                <?php if($details->old_price): ?>
                                <div class="product-details-discount-badge">
                                    <div class="sale-badge">
                                        <div class="sale-badge-inner">
                                            <div class="sale-badge-box">
                                                <span class="sale-badge-text">
                                                    <p> <?php $discount=(((($details->old_price)-($details->new_price))*100) / ($details->old_price)) ?> <?php echo e(number_format($discount, 0)); ?>%</p>
                                                    ছাড়
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="details_slider owl-carousel" id="details_slider_main">
                                    <?php $__currentLoopData = $details->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="dimage_item" data-id="<?php echo e($key); ?>" data-color-id="<?php echo e($value->color_id ?? ''); ?>" data-size-id="<?php echo e($value->size_id ?? ''); ?>">
                                            <img src="<?php echo e(asset($value->image)); ?>" class="block__pic" />
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <div
                                    class="indicator_thumb <?php if($details->images->count() > 4): ?> thumb_slider owl-carousel <?php endif; ?>" id="indicator_thumb_wrapper">
                                    <?php $__currentLoopData = $details->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="indicator-item <?php echo e($loop->first ? 'active' : ''); ?>" data-id="<?php echo e($key); ?>" data-color-id="<?php echo e($image->color_id ?? ''); ?>" data-size-id="<?php echo e($image->size_id ?? ''); ?>">
                                            <img src="<?php echo e(asset($image->image)); ?>" />
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="details_right">
                                    <div class="breadcrumb">
                                        <ul>
                                            <li><a href="<?php echo e(url('/')); ?>">Home</a></li>
                                            <li><span>/</span></li>
                                            <li><a
                                                    href="<?php echo e(url('/category/' . $details->category->slug)); ?>"><?php echo e($details->category->name); ?></a>
                                            </li>
                                            <?php if($details->subcategory): ?>
                                                <li><span>/</span></li>
                                                <li><a
                                                        href="#"><?php echo e($details->subcategory ? $details->subcategory->subcategoryName : ''); ?></a>
                                                </li>
                                                <?php endif; ?> <?php if($details->childcategory): ?>
                                                    <li><span>/</span></li>
                                                    <li><a
                                                            href="#"><?php echo e($details->childcategory->childcategoryName); ?></a>
                                                    </li>
                                                <?php endif; ?>
                                        </ul>
                                    </div>

                                    <div class="product">
                                        <div class="product-cart">
                                            <p class="name"><?php echo e($details->name); ?></p>
                                            <p class="details-price">
                                                <del id="oldPrice" style="<?php echo e(($details->old_price && $details->old_price > $displayPrice) ? '' : 'display:none;'); ?>">৳<?php echo e($details->old_price); ?></del>
                                                <span id="newPrice">৳<?php echo e($displayPrice); ?></span>
                                            </p>
                                            <div class="details-ratting-wrapper">
                                            <?php
                                                $averageRating = $reviews->avg('ratting');
                                                $filledStars = floor($averageRating);
                                                $emptyStars = 5 - $filledStars;
                                            ?>
                                            
                                            <?php if($averageRating >= 0 && $averageRating <= 5): ?>
                                                <?php for($i = 1; $i <= $filledStars; $i++): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                            
                                                <?php if($averageRating == $filledStars): ?>
                                                    
                                                <?php else: ?>
                                                    <i class="far fa-star-half-alt"></i>
                                                <?php endif; ?>
                                            
                                                <?php for($i = 1; $i <= $emptyStars; $i++): ?>
                                                    <i class="far fa-star"></i>
                                                <?php endfor; ?>
                                            
                                                <span><?php echo e(number_format($averageRating, 2)); ?>/5</span>
                                            <?php else: ?>
                                                <span>Invalid rating range</span>
                                            <?php endif; ?>
                                            <a class="all-reviews-button" href="#writeReview">See Reviews</a>
                                            </div>
                                            <div class="product-code">
                                                <p><span>প্রোডাক্ট কোড : </span><?php echo e($details->product_code); ?></p>
                                            </div>

                                            
                                            <?php
                                                $productTypeText = $details->is_digital
                                                    ? 'Digital'
                                                    : 'Physical';
                                            ?>
                                            <div class="pro_brand">
                                                <p>
                                                  Product Type: <?php echo e($productTypeText); ?>

                                                </p>
                                            </div>
                                            

                                            
                                            <?php if($details->is_wholesale && $details->wholesalePrices && $details->wholesalePrices->count() > 0): ?>
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
                                                            <?php $__currentLoopData = $details->wholesalePrices->sortBy('min_quantity'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr class="wholesale-tier-row" 
                                                                data-min-qty="<?php echo e($tier->min_quantity); ?>" 
                                                                data-max-qty="<?php echo e($tier->max_quantity ?? 999999); ?>" 
                                                                data-price="<?php echo e($tier->wholesale_price); ?>"
                                                                style="cursor: pointer; transition: background 0.2s;">
                                                                <td style="padding: 12px; font-size: 14px;">
                                                                    <?php echo e($tier->min_quantity); ?><?php echo e($tier->max_quantity ? ' - ' . $tier->max_quantity : '+'); ?> pcs
                                                                </td>
                                                                <td style="padding: 12px; font-size: 14px; font-weight: 600; color: #28a745;">
                                                                    ৳<?php echo e(number_format($tier->wholesale_price, 2)); ?>

                                                                </td>
                                                                <td style="padding: 12px; font-size: 14px; color: <?php echo e(($tier->stock ?? 0) > 0 ? '#28a745' : '#dc3545'); ?>;">
                                                                    <?php echo e($tier->stock ?? 0); ?> pcs
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p class="text-muted mt-2 mb-0" style="font-size: 12px;">
                                                    <i class="fa fa-info-circle me-1"></i> Quantity select করলে wholesale price automatically apply হবে
                                                </p>
                                            </div>
                                            <?php endif; ?>
                                            

                                            <form action="<?php echo e(route('cart.store')); ?>" method="POST" name="formName" class="ajax-cart-form" id="productDetailsCartForm" onsubmit="return handleDetailsCartSubmit(event)">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="id" value="<?php echo e($details->id); ?>" />



<?php if($details->variantPrices->count() > 0): ?>
    <?php
        $productcolors = $details->variantPrices->pluck('color')->unique('id')->filter();
        $productsizes = $details->variantPrices->pluck('size')->unique('id')->filter();
    ?>

    
    <?php if($productcolors->count() > 0): ?>
        <div class="pro-color" style="width: 100%;">
            <div class="color_inner">
                <p>Color -</p>
                <div class="size-container">
                    <div class="selector">
                        <?php $__currentLoopData = $productcolors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $procolor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="selector-item">
                                
                                <input type="radio"
                                    id="fc-option<?php echo e($procolor->id); ?>"
                                    value="<?php echo e($procolor->id); ?>"
                                    name="product_color"
                                    class="selector-item_radio emptyalert"
                                    <?php echo e($loop->first ? 'checked' : ''); ?>

                                    required />
                                <label for="fc-option<?php echo e($procolor->id); ?>"
                                    style="background-color: <?php echo e($procolor->color ?? '#ccc'); ?>"
                                    class="selector-item_label">
                                    <span>
                                        <img src="<?php echo e(asset('public/frontEnd/images/check-icon.svg')); ?>" alt="Checked Icon" />
                                    </span>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if($productsizes->count() > 0): ?>
        <div class="pro-size" style="width: 100%;">
            <div class="size_inner">
                <p>Size & Variant - <span class="attibute-name"></span></p>
                <div class="size-container">
                    <div class="selector">
                        <?php $__currentLoopData = $productsizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prosize): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="selector-item">
                                
                                <input type="radio"
                                    id="f-option<?php echo e($prosize->id); ?>"
                                    value="<?php echo e($prosize->id); ?>"
                                    name="product_size"
                                    class="selector-item_radio emptyalert"
                                    <?php echo e($loop->first ? 'checked' : ''); ?>

                                    required />
                                <label for="f-option<?php echo e($prosize->id); ?>" class="selector-item_label">
                                    <?php echo e($prosize->sizeName ?? $prosize->name); ?>

                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>





                                                        <?php if($details->pro_unit): ?>
                                                            <div class="pro_unig">
                                                                <label>Unit: <?php echo e($details->pro_unit); ?></label>
                                                                <input type="hidden" name="pro_unit"
                                                                    value="<?php echo e($details->pro_unit); ?>" />
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="pro_brand">
                                                            <p>Brand :
                                                                <?php echo e($details->brand ? $details->brand->name : 'N/A'); ?>

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
                                                            <div class="d-flex single_product col-sm-12">
                                                  <input type="submit" class="btn px-4 add_cart_btn" onclick="return sendSuccess();" name="add_cart" value="কার্টে যোগ করুন" />
<input type="submit" class="btn px-4 order_now_btn order_now_btn_m" onclick="return sendSuccess();" name="order_now" value="অর্ডার করুন" />
                                                            </div>
                                                        </div>
                                                        <div class="mt-md-2 mt-2">
                                                            <h4 class="font-weight-bold">
                                                                <a class="btn btn-success w-100 call_now_btn"
                                                                    href="tel: <?php echo e($contact->hotline); ?>">
                                                                    <i class="fa fa-phone-square"></i>
                                                                    <?php echo e($contact->hotline); ?>

                                                                </a>
                                                            </h4>
                                                        </div>
                                                       <div class="mt-md-2 mt-2">
                                                        <h4 class="font-weight-bold">
                                                            <a class="btn btn-success w-100 call_now_btn"
                                                                href="https://api.whatsapp.com/send?phone=<?php echo e($contact->whatsapp); ?>&text=হ্যালো, আমি এই পণ্যটির ব্যাপারে জানতে চাই: <?php echo e(urlencode(Request::url())); ?>"
                                                                target="_blank">
                                                                <i class="fa fa-whatsapp"></i>
                                                                এই পণ্যটি সম্পর্কে জিজ্ঞাসা করুন
                                                            </a>
                                                        </h4>
                                                    </div>

                                                        <div class="mt-md-2 mt-2">
                                                            <div class="del_charge_area">
                                                                <div class="alert alert-info text-xs">
                                                                    <div class="flext_area">
                                                                        <i class="fa-solid fa-cubes"></i>
                                                                        <div>

                                                                            <?php $__currentLoopData = $shippingcharge; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <span><?php echo e($value->name); ?> <br /></span>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        
                        <li>
                            <a href="#description" target="_self">Description</a>
                        </li>
                        
                        <li>
                            <a href="#writeReview" target="_self">Reviews (<?php echo e($reviews->count()); ?>) </a>
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
                    <p><?php echo $details->description; ?></p>
                </div>
                <div class="tab-content details-action-box" id="writeReview">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                
							  
							  
							  
							<section class="gomobd-review-section mt-5" id="reviewsList">
    <div class="gomobd-review-header d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h3 class="gomobd-review-title fw-bold mb-2 mb-md-0">
            Customer Reviews (<?php echo e($reviews->count()); ?>)
        </h3>
        <button type="button" class="gomobd-review-btn btn btn-success btn-sm"
            data-bs-toggle="modal" data-bs-target="#exampleModal">
            <i class="fa fa-edit me-1"></i> Write a Review
        </button>
    </div>

    <?php if($reviews->count() > 0): ?>
    <div class="gomobd-review-list row g-3">
        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-12">
            <div class="gomobd-review-card shadow-sm">
                <div class="gomobd-review-card-header d-flex justify-content-between align-items-start flex-wrap">
                    <div class="d-flex align-items-center">
                        <div class="gomobd-review-avatar">
                            <?php echo e(strtoupper(substr($review->name, 0, 1))); ?>

                        </div>
                        <div class="gomobd-review-meta">
                            <h6 class="gomobd-review-name"><?php echo e($review->name); ?></h6>
                            <small class="gomobd-review-date"><?php echo e($review->created_at->format('d M Y')); ?></small>
                        </div>
                    </div>
                    <div class="gomobd-review-stars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if($i <= $review->ratting): ?>
                                <i class="fa-solid fa-star"></i>
                            <?php else: ?>
                                <i class="fa-regular fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="gomobd-review-body mt-2">
                    <p><i class="fa-regular fa-comment-dots text-success me-1"></i> <?php echo e($review->review); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php else: ?>
    <div class="gomobd-review-empty text-center py-5">
        <i class="fa fa-clipboard-list fs-1 text-muted mb-3"></i>
        <p>This product has no reviews yet.<br><strong>Be the first one to write a review.</strong></p>
    </div>
    <?php endif; ?>
</section>

                            </div>
                        </div>
                    </div>
                </div>
            <?php
                $videoType = $details->pro_video_type ?? ($details->pro_video ? 'youtube' : null);
                $hasVideo = ($videoType === 'youtube' && $details->pro_video) || ($videoType === 'upload' && $details->pro_video_path);
            ?>
            <?php if($hasVideo): ?>
            <div class="col-sm-4">
                <div class="pro_vide">
                    <h2>ভিডিও</h2>
                    <?php if($videoType === 'youtube' && $details->pro_video): ?>
                    <iframe width="100%" height="315"
                        src="https://www.youtube.com/embed/<?php echo e($details->pro_video); ?>" title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>
                    <?php elseif($videoType === 'upload' && $details->pro_video_path): ?>
                    <video width="100%" height="315" controls style="border-radius:8px;background:#000;">
                        <source src="<?php echo e(asset($details->pro_video_path)); ?>" type="video/mp4">
                        <source src="<?php echo e(asset($details->pro_video_path)); ?>" type="video/webm">
                        <source src="<?php echo e(asset($details->pro_video_path)); ?>" type="video/ogg">
                        আপনার ব্রাউজার ভিডিও সাপোর্ট করে না।
                    </video>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
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
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="product_item wist_item wow zoomIn" data-wow-duration="1.5s"
                        data-wow-delay="0.<?php echo e($key); ?>s">

                        <div class="product_item_inner">
                            <?php if($value->old_price): ?>
                            <div class="sale-badge">
                                <div class="sale-badge-inner">
                                    <div class="sale-badge-box">
                                        <span class="sale-badge-text">
                                            <p><?php $discount=(((($value->old_price)-($value->new_price))*100) / ($value->old_price)) ?> 
                                               <?php echo e(number_format($discount, 0)); ?>%</p>
                                            ছাড়
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="pro_img">
                                <a href="<?php echo e(route('product', $value->slug)); ?>">
                                    <img src="<?php echo e(asset($value->image ? $value->image->image : '')); ?>"
                                        alt="<?php echo e($value->name); ?>" />
                                </a>
                            </div>

                            <div class="pro_des">
                                <div class="pro_name">
                                    <a href="<?php echo e(route('product', $value->slug)); ?>"><?php echo e(Str::limit($value->name, 35)); ?></a>
                                </div>
                            </div>
                        </div>

                        <?php
                            $averageRating = $value->reviews->avg('ratting'); 
                            $filledStars = floor($averageRating);
                            $hasHalfStar = $averageRating - $filledStars >= 0.5;
                            $emptyStars = 5 - $filledStars - ($hasHalfStar ? 1 : 0);
                        ?>

                        
                        <?php for($i = 0; $i < $filledStars; $i++): ?>
                            <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        <?php if($hasHalfStar): ?>
                            <i class="fas fa-star-half-alt"></i>
                        <?php endif; ?>
                        <?php for($i = 0; $i < $emptyStars; $i++): ?>
                            <i class="far fa-star"></i>
                        <?php endfor; ?>

                        <?php
                            $cardPrice = ($value->variantPrices && $value->variantPrices->isNotEmpty() && $value->variantPrices->first()->price > 0)
                                ? $value->variantPrices->first()->price 
                                : $value->new_price;
                            $hasVariants = ($value->variantPrices && $value->variantPrices->isNotEmpty()) 
                                || ($value->prosizes && $value->prosizes->isNotEmpty()) 
                                || ($value->procolors && $value->procolors->isNotEmpty());
                        ?>
                        <div class="pro_price">
                            <p>
                                <?php if($value->old_price && $value->old_price > $cardPrice): ?>
                                    <del>৳ <?php echo e($value->old_price); ?></del>
                                <?php endif; ?>
                                ৳ <?php echo e($cardPrice); ?>

                            </p>
                        </div>

                        
                        <?php if(!is_null($value->stock) && $value->stock < 1): ?>
                            <div class="pro_btn">
                                <a href="<?php echo e(route('product', $value->slug)); ?>" class="order-btn-link order-btn">
                                    স্টক আউট
                                </a>
                                <a href="<?php echo e(route('product', $value->slug)); ?>" class="cart-icon-link cart-icon-btn">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </a>
                            </div>
                        <?php elseif($hasVariants): ?>
                            <div class="pro_btn">
                                <button type="button" class="order-btn open-variant-modal" data-id="<?php echo e($value->id); ?>" data-action="order_now">
                                    অর্ডার করুন
                                </button>
                                <button type="button" class="cart-icon-btn open-variant-modal" data-id="<?php echo e($value->id); ?>" data-action="add_cart">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </button>
                            </div>
                        <?php else: ?>
                            
                            <div class="pro_btn">
                                
                                <form action="<?php echo e(route('cart.store')); ?>" method="POST" class="ajax-cart-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo e($value->id); ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <input type="hidden" name="order_now" value="1">

                                    <button type="submit" class="order-btn">
                                        অর্ডার করুন
                                    </button>
                                </form>

                                
                                <form action="<?php echo e(route('cart.store')); ?>" method="POST" class="ajax-cart-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo e($value->id); ?>">
                                    <input type="hidden" name="qty" value="1">

                                    <button type="submit" class="cart-icon-btn">
                                        <i class="fa-solid fa-cart-shopping"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</section>


<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Your review</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="insert-review">
                    <?php if(Auth::guard('customer')->user()): ?>
                        <form action="<?php echo e(route('customer.review')); ?>" id="review-form" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="product_id" value="<?php echo e($details->id); ?>">
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
                    <?php else: ?>
                        <a class="customer-login-redirect" href="<?php echo e(route('customer.login')); ?>">Login to Post Your Review</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?> <?php $__env->startPush('script'); ?>
<script src="<?php echo e(asset('public/frontEnd/js/owl.carousel.min.js')); ?>"></script>

<script src="<?php echo e(asset('public/frontEnd/js/zoomsl.min.js')); ?>"></script>
<script>
    // Review modal কে body তে সরিয়ে দেওয়া - #content এর z-index:1 এর জন্য modal backdrop এর পিছনে পড়ছিল
    document.addEventListener('DOMContentLoaded', function() {
        var m = document.getElementById('exampleModal');
        if (m && document.body) document.body.appendChild(m);
    });
</script>
<script>
    const variants = <?php echo json_encode($details->variantPrices, 15, 512) ?>;

    <?php if($details->is_wholesale && $details->wholesalePrices && $details->wholesalePrices->count() > 0): ?>
    var wholesaleTiers = [
        <?php $__currentLoopData = $details->wholesalePrices->sortBy('min_quantity'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        {
            min_quantity: <?php echo e($tier->min_quantity); ?>,
            max_quantity: <?php echo e($tier->max_quantity ?? 999999); ?>,
            price: <?php echo e($tier->wholesale_price); ?>

        }<?php if(!$loop->last): ?>,<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    ];
    var regularPrice = <?php echo e($details->new_price); ?>;

    function getWholesalePrice(qty) {
        for (var i = 0; i < wholesaleTiers.length; i++) {
            if (qty >= wholesaleTiers[i].min_quantity && qty <= wholesaleTiers[i].max_quantity) {
                return wholesaleTiers[i].price;
            }
        }
        return null;
    }
    <?php endif; ?>

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

        let basePrice = parseFloat(<?php echo e((float)$displayPrice); ?>);
        if (match && match.price !== undefined && match.price !== null && parseFloat(match.price) > 0) {
            basePrice = parseFloat(match.price);
        }

        // Apply wholesale price if applicable (wholesale price overrides variant price)
        <?php if($details->is_wholesale && $details->wholesalePrices && $details->wholesalePrices->count() > 0): ?>
        let qty = parseInt($("input[name='qty']").val()) || 1;
        let wholesalePrice = getWholesalePrice(qty);
        if (wholesalePrice !== null) {
            basePrice = parseFloat(wholesalePrice);
        }
        <?php endif; ?>

        $('#newPrice').text('৳' + basePrice.toFixed(2));

        // Update Old Price display
        let oldPriceVal = parseFloat(<?php echo e((float)($details->old_price ?? 0)); ?>);
        if (oldPriceVal > basePrice) {
            $('#oldPrice').show().text('৳' + oldPriceVal.toFixed(2));
        } else {
            $('#oldPrice').hide();
        }
    }

    // ভ্যারিয়েন্ট ইমেজ লিস্ট (কালার ও সাইজ উভয়টি অন্তর্ভুক্ত)
    <?php
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
    ?>
    var productImages = <?php echo json_encode($productImagesList); ?>;
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
        <?php if($details->is_wholesale && $details->wholesalePrices && $details->wholesalePrices->count() > 0): ?>
        var wholesaleTiers = [
            <?php $__currentLoopData = $details->wholesalePrices->sortBy('min_quantity'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            {
                min_quantity: <?php echo e($tier->min_quantity); ?>,
                max_quantity: <?php echo e($tier->max_quantity ?? 999999); ?>,
                price: <?php echo e($tier->wholesale_price); ?>

            }<?php if(!$loop->last): ?>,<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        ];
        var regularPrice = <?php echo e($details->new_price); ?>;

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
        <?php endif; ?>
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
        var atcEventId = 'atc_' + '<?php echo e($details->id); ?>' + '_' + Math.floor(Date.now() / 1000);
        formData.append("event_id", atcEventId);

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "<?php echo e(route('cart.store')); ?>");
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
                        var serverPrice = (data && data.price) ? parseFloat(data.price) : <?php echo e((float)($details->new_price ?? 0)); ?>;
                        var serverQty   = (data && data.qty)   ? parseInt(data.qty)     : qty;
                        fireAddToCartEvent(null, '<?php echo e($details->id); ?>', serverQty, <?php echo json_encode($details->name, 15, 512) ?>, serverPrice, <?php echo json_encode(optional($details->category)->name ?? '', 15, 512) ?>, (data && data.event_id) ? data.event_id : atcEventId);
                    }
                    if (typeof toastr !== "undefined") toastr.success("কার্টে যোগ হয়েছে!", "সফল");
                    if (typeof cart_count === "function") cart_count();
                    if (typeof mobile_cart === "function") mobile_cart();
                    if (typeof sidebarCartRefresh === "function") sidebarCartRefresh();
                    if (data.redirect || isOrderNow) {
                        window.location.href = data.redirect || "<?php echo e(route('customer.checkout')); ?>";
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

<?php $__env->stopPush(); ?>

<?php echo $__env->make('frontEnd.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\ecommerce\smartgrammobazar\resources\views/frontEnd/layouts/pages/details.blade.php ENDPATH**/ ?>