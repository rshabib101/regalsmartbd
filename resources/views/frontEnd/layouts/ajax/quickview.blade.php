@php
    $productColors = collect();
    $productSizes  = collect();

    if ($data->variantPrices && $data->variantPrices->count() > 0) {
        $productColors = $data->variantPrices->pluck('color')->unique('id')->filter();
        $productSizes  = $data->variantPrices->pluck('size')->unique('id')->filter();
    } elseif ($data->prosizes || $data->procolors) {
        $productSizes  = $data->prosizes ? $data->prosizes->pluck('size')->unique('id')->filter() : collect();
        $productColors = $data->procolors ? $data->procolors->pluck('color')->unique('id')->filter() : collect();
    }

    $firstVariant = ($data->variantPrices && $data->variantPrices->count() > 0) ? $data->variantPrices->first() : null;
    $initialPrice = ($firstVariant && $firstVariant->price > 0) ? $firstVariant->price : ($data->new_price ?? 0);
    $initialOldPrice = $data->old_price ?? 0;

    $initialDiscount = ($initialOldPrice && $initialOldPrice > $initialPrice) 
        ? round((($initialOldPrice - $initialPrice) * 100) / $initialOldPrice) 
        : 0;

    $mainImage = ($data->image && $data->image->image) ? asset($data->image->image) : asset('public/frontEnd/images/no-image.png');

    $modalImages = $data->images ? $data->images->map(function($img) {
        return [
            'src'      => asset($img->image),
            'color_id' => $img->color_id ? (string)$img->color_id : null,
            'size_id'  => $img->size_id ? (string)$img->size_id : null,
        ];
    }) : collect();

    $modalVariants = ($data->variantPrices && $data->variantPrices->count() > 0) ? $data->variantPrices->map(function($v) {
        return [
            'color_id' => $v->color_id ? (string)$v->color_id : null,
            'size_id'  => $v->size_id ? (string)$v->size_id : null,
            'price'    => (float)$v->price,
            'stock'    => (int)$v->stock,
        ];
    }) : collect();
@endphp

<div class="modal-view quick-variant-modal shadow-lg">
    <button type="button" class="close-modal" id="quickModalCloseBtn" title="Close">&times;</button>
    
    <div class="quick-modal-body">
        <div class="row g-3 align-items-center">
            {{-- Left: Product Image --}}
            <div class="col-sm-5 text-center">
                <div class="quick-modal-img-wrap">
                    <img src="{{ $mainImage }}" id="quickModalImg" alt="{{ $data->name }}" class="img-fluid rounded">
                </div>
            </div>

            {{-- Right: Details & Variant Selection --}}
            <div class="col-sm-7">
                <div class="quick-modal-content-wrap">
                    <h4 class="quick-modal-title mb-1">{{ $data->name }}</h4>
                    
                    @if(optional($data->category)->name)
                        <div class="quick-modal-category text-muted small mb-2">
                            <span>ক্যাটাগরি: <strong>{{ $data->category->name }}</strong></span>
                            @if(optional($data->brand)->name)
                                <span class="ms-2">| ব্র্যান্ড: <strong>{{ $data->brand->name }}</strong></span>
                            @endif
                        </div>
                    @endif

                    {{-- Pricing Display --}}
                    <div class="quick-modal-price-area mb-3">
                        <span class="quick-price-currency">৳</span>
                        <span class="quick-new-price" id="quickModalNewPrice">{{ number_format($initialPrice, 0) }}</span>
                        
                        <del class="quick-old-price ms-2 {{ $initialOldPrice > $initialPrice ? '' : 'd-none' }}" id="quickModalOldPrice">
                            ৳{{ number_format($initialOldPrice, 0) }}
                        </del>
                        
                        <span class="badge bg-danger ms-2 quick-discount-tag {{ $initialDiscount > 0 ? '' : 'd-none' }}" id="quickModalDiscount">
                            {{ $initialDiscount }}% ছাড়
                        </span>
                    </div>

                    {{-- Form --}}
                    <form action="{{ route('cart.store') }}" method="POST" class="ajax-cart-form" id="quickModalCartForm">
                        @csrf
                        <input type="hidden" name="id" value="{{ $data->id }}" />

                        {{-- 🎨 Color Selection --}}
                        @if ($productColors->count() > 0)
                            <div class="quick-option-group mb-3">
                                <label class="quick-option-label fw-bold small text-dark d-block mb-1">কালার সিলেক্ট করুন:</label>
                                <div class="quick-color-pills d-flex flex-wrap gap-2">
                                    @foreach ($productColors as $color)
                                        <label class="quick-color-pill m-0">
                                            <input type="radio" 
                                                   name="product_color" 
                                                   value="{{ $color->id }}" 
                                                   class="quick-color-input"
                                                   {{ $loop->first ? 'checked' : '' }} 
                                                   required />
                                            <span class="quick-color-box" style="background-color: {{ $color->color ?? '#e2e8f0' }};" title="{{ $color->colorName ?? $color->name }}">
                                                <i class="fa fa-check quick-check-icon"></i>
                                            </span>
                                            <span class="quick-color-name small ms-1">{{ $color->colorName ?? $color->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- 📏 Size / Weight Selection --}}
                        @if ($productSizes->count() > 0)
                            <div class="quick-option-group mb-3">
                                <label class="quick-option-label fw-bold small text-dark d-block mb-1">সাইজ / ওজন সিলেক্ট করুন:</label>
                                <div class="quick-size-pills d-flex flex-wrap gap-2">
                                    @foreach ($productSizes as $size)
                                        <label class="quick-size-pill m-0">
                                            <input type="radio" 
                                                   name="product_size" 
                                                   value="{{ $size->id }}" 
                                                   class="quick-size-input"
                                                   {{ $loop->first ? 'checked' : '' }} 
                                                   required />
                                            <span class="quick-size-btn">
                                                {{ $size->sizeName ?? $size->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Quantity Selector --}}
                        <div class="quick-qty-group d-flex align-items-center mb-3">
                            <span class="fw-bold small text-dark me-3">পরিমাণ:</span>
                            <div class="quick-qty-controls d-flex align-items-center border rounded">
                                <button type="button" class="btn btn-sm btn-light quick-qty-minus px-3 py-1" style="border:none;">-</button>
                                <input type="number" name="qty" id="quickModalQty" value="1" min="1" class="form-control text-center form-control-sm border-0" style="width: 50px; font-weight: bold;">
                                <button type="button" class="btn btn-sm btn-light quick-qty-plus px-3 py-1" style="border:none;">+</button>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="quick-modal-actions row g-2">
                            <div class="col-6">
                                <button type="submit" name="order_now" value="1" class="btn btn-danger w-100 quick-btn-order fw-bold py-2 shadow-sm">
                                    <i class="fa-solid fa-bolt me-1"></i> অর্ডার করুন
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="submit" class="btn btn-dark w-100 quick-btn-cart fw-bold py-2 shadow-sm">
                                    <i class="fa-solid fa-cart-shopping me-1"></i> কার্টে যোগ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Quick Modal Styling */
.quick-variant-modal {
    position: fixed;
    width: 92%;
    max-width: 600px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #ffffff;
    z-index: 99999;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    border: none;
    animation: quickModalFadeIn 0.25s ease-out;
}
@keyframes quickModalFadeIn {
    from { opacity: 0; transform: translate(-50%, -46%) scale(0.96); }
    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
.quick-variant-modal .close-modal {
    position: absolute;
    right: 12px;
    top: 12px;
    background: #f1f5f9;
    color: #334155;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 20px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    z-index: 10;
}
.quick-variant-modal .close-modal:hover {
    background: #e2e8f0;
    color: #ef4444;
}
.quick-modal-img-wrap {
    background: #f8fafc;
    border-radius: 10px;
    padding: 10px;
    border: 1px solid #e2e8f0;
}
.quick-modal-img-wrap img {
    max-height: 220px;
    width: auto;
    object-fit: contain;
    transition: all 0.25s ease;
}
.quick-modal-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.35;
    padding-right: 20px;
}
.quick-modal-price-area {
    display: flex;
    align-items: baseline;
}
.quick-price-currency {
    font-size: 18px;
    font-weight: 700;
    color: #059669;
}
.quick-new-price {
    font-size: 26px;
    font-weight: 800;
    color: #059669;
    margin-left: 2px;
}
.quick-old-price {
    font-size: 16px;
    color: #94a3b8;
    text-decoration: line-through;
}
.quick-discount-tag {
    font-size: 12px;
    font-weight: 600;
    border-radius: 4px;
    padding: 3px 8px;
}
/* Size Pill Selectors (Naturo Style) */
.quick-size-pill {
    cursor: pointer;
    user-select: none;
}
.quick-size-input {
    display: none;
}
.quick-size-btn {
    display: inline-block;
    padding: 6px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 25px;
    background: #ffffff;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    transition: all 0.2s ease;
}
.quick-size-pill input:checked + .quick-size-btn {
    border-color: #059669;
    background: #ecfdf5;
    color: #059669;
    box-shadow: 0 2px 6px rgba(5, 150, 105, 0.2);
}
/* Color Swatches */
.quick-color-pill {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 3px 10px 3px 4px;
    background: #ffffff;
    transition: all 0.2s ease;
}
.quick-color-input {
    display: none;
}
.quick-color-box {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(0,0,0,0.1);
}
.quick-check-icon {
    font-size: 10px;
    color: #ffffff;
    display: none;
    text-shadow: 0 1px 2px rgba(0,0,0,0.6);
}
.quick-color-pill input:checked ~ .quick-color-box .quick-check-icon {
    display: block;
}
.quick-color-pill input:checked + .quick-color-box {
    box-shadow: 0 0 0 2px #059669;
}
.quick-color-pill:has(input:checked) {
    border-color: #059669;
    background: #f0fdf4;
}
/* Buttons */
.quick-btn-order {
    background: #2563eb;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
}
.quick-btn-order:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}
.quick-btn-cart {
    background: #0f172a;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
}
.quick-btn-cart:hover {
    background: #1e293b;
    transform: translateY(-1px);
}

@media (max-width: 576px) {
    .quick-variant-modal {
        width: 95%;
        padding: 16px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .quick-modal-img-wrap img {
        max-height: 140px;
    }
    .quick-modal-title {
        font-size: 16px;
    }
    .quick-new-price {
        font-size: 22px;
    }
}
</style>

<script>
(function() {
    var modalVariants = @json($modalVariants);
    var modalImages   = @json($modalImages);
    var baseProductPrice = parseFloat({{ (float)($data->new_price ?? 0) }});
    var baseOldPrice = parseFloat({{ (float)($data->old_price ?? 0) }});

    function updateQuickModalState() {
        var selectedColor = $("input[name='product_color']:checked").val() || null;
        var selectedSize  = $("input[name='product_size']:checked").val() || null;

        // 1. Match Variant Price
        var match = null;
        if (selectedColor && selectedSize) {
            match = modalVariants.find(function(v) {
                return String(v.color_id) === String(selectedColor) && String(v.size_id) === String(selectedSize);
            });
        }
        if (!match && selectedSize && !selectedColor) {
            match = modalVariants.find(function(v) {
                return String(v.size_id) === String(selectedSize) && (!v.color_id || v.color_id === '');
            });
        }
        if (!match && selectedColor && !selectedSize) {
            match = modalVariants.find(function(v) {
                return String(v.color_id) === String(selectedColor) && (!v.size_id || v.size_id === '');
            });
        }
        if (!match && selectedSize) {
            // fallback matching size
            match = modalVariants.find(function(v) {
                return String(v.size_id) === String(selectedSize);
            });
        }

        var price = baseProductPrice;
        if (match && match.price && match.price > 0) {
            price = parseFloat(match.price);
        } else if (modalVariants.length > 0 && modalVariants[0].price > 0) {
            price = parseFloat(modalVariants[0].price);
        }

        $("#quickModalNewPrice").text(Math.round(price).toLocaleString());

        if (baseOldPrice > price) {
            $("#quickModalOldPrice").removeClass("d-none").text('৳' + Math.round(baseOldPrice).toLocaleString());
            var discount = Math.round(((baseOldPrice - price) * 100) / baseOldPrice);
            $("#quickModalDiscount").removeClass("d-none").text(discount + '% ছাড়');
        } else {
            $("#quickModalOldPrice").addClass("d-none");
            $("#quickModalDiscount").addClass("d-none");
        }

        // 2. Match Variant Image
        var matchedImg = null;
        if (selectedColor && selectedSize) {
            matchedImg = modalImages.find(function(img) {
                return img.color_id === String(selectedColor) && img.size_id === String(selectedSize);
            });
        }
        if (!matchedImg && selectedSize) {
            matchedImg = modalImages.find(function(img) {
                return img.size_id === String(selectedSize);
            });
        }
        if (!matchedImg && selectedColor) {
            matchedImg = modalImages.find(function(img) {
                return img.color_id === String(selectedColor);
            });
        }

        if (matchedImg && matchedImg.src) {
            $("#quickModalImg").attr("src", matchedImg.src);
        }
    }

    // Event listeners for radio change
    $(document).off("change.quickModal", "input[name='product_color'], input[name='product_size']");
    $(document).on("change.quickModal", "input[name='product_color'], input[name='product_size']", function() {
        updateQuickModalState();
    });

    // Close button
    $("#quickModalCloseBtn").off("click").on("click", function(e) {
        e.preventDefault();
        $("#custom-modal").fadeOut(150, function() { $(this).empty(); });
        $("#page-overlay").fadeOut(150);
    });

    // Quantity buttons
    $(".quick-qty-minus").off("click").on("click", function() {
        var $input = $("#quickModalQty");
        var val = parseInt($input.val()) || 1;
        if (val > 1) {
            $input.val(val - 1).trigger("change");
        }
    });
    $(".quick-qty-plus").off("click").on("click", function() {
        var $input = $("#quickModalQty");
        var val = parseInt($input.val()) || 1;
        $input.val(val + 1).trigger("change");
    });

    // Initial run
    updateQuickModalState();
})();
</script>