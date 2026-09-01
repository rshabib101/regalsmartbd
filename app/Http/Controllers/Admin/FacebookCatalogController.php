<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\GeneralSetting;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacebookCatalogController extends Controller
{
    /**
     * Display the Facebook Catalog setup and data feed settings.
     */
    public function index()
    {
        $totalProducts   = Product::where('status', 1)->where('in_facebook_catalog', 1)->count();
        $inStockProducts = Product::where('status', 1)->where('in_facebook_catalog', 1)->where('stock', '>', 0)->count();
        $totalCategories = Category::where('status', 1)->count();
        $feedUrl         = url('facebook-catalog.csv');
        $xmlFeedUrl      = url('facebook-catalog.xml');

        return view('backEnd.settings.facebook_catalog', compact(
            'totalProducts',
            'inStockProducts',
            'totalCategories',
            'feedUrl',
            'xmlFeedUrl'
        ));
    }

    /**
     * Generate dynamic live CSV feed for Facebook / Meta Commerce Manager.
     */
    public function feed()
    {
        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for full UTF-8 character support
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Standard Meta Commerce Manager Catalog CSV Headers
            fputcsv($handle, [
                'id',
                'title',
                'item_group_id',
                'description',
                'availability',
                'condition',
                'sku',
                'price',
                'sale_price',
                'link',
                'image_link',
                'brand',
                'google_product_category',
                'fb_product_category',
                'size',
                'color',
                'additional_image_link',
            ]);

            $siteName = GeneralSetting::first()?->name ?? config('app.name', 'Smart Grammo Bazar');

            // Chunk through products to be memory efficient
            Product::where('status', 1)
                ->where(function($q) {
                    $q->whereNull('in_facebook_catalog')->orWhere('in_facebook_catalog', 1);
                })
                ->with(['image', 'images', 'category', 'brand', 'variantPrices.size', 'variantPrices.color'])
                ->chunk(200, function ($products) use ($handle, $siteName) {
                    foreach ($products as $product) {
                        $this->writeProductRows($handle, $product, $siteName);
                    }
                });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'inline; filename="facebook_catalog_feed.csv"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Generate dynamic live XML / Google Merchant RSS Feed for Meta Commerce Manager.
     */
    public function xmlFeed()
    {
        $siteName    = GeneralSetting::first()?->name ?? config('app.name', 'Smart Grammo Bazar');
        $products    = Product::where('status', 1)
            ->where(function($q) {
                $q->whereNull('in_facebook_catalog')->orWhere('in_facebook_catalog', 1);
            })
            ->with(['image', 'images', 'category', 'brand', 'variantPrices.size', 'variantPrices.color'])
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '<title>' . htmlspecialchars($siteName, ENT_XML1) . ' Product Catalog</title>' . "\n";
        $xml .= '<link>' . htmlspecialchars(url('/'), ENT_XML1) . '</link>' . "\n";
        $xml .= '<description>Live product feed for Meta Commerce Manager and Google Merchant Center</description>' . "\n";

        foreach ($products as $product) {
            $rawDescription = $product->description ?: $product->name;
            $cleanDesc = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($rawDescription, ENT_QUOTES, 'UTF-8')))) ?: $product->name;
            $brandName = $product->brand?->name ?: $siteName;
            $catName   = $product->category?->name ?: 'General';
            $prodLink  = route('product', $product->slug);
            $imagePath = $product->image?->image ?? '';
            $imageUrl  = $this->resolveImageUrl($imagePath);

            $hasVariants = $product->variantPrices && $product->variantPrices->count() > 0;

            if ($hasVariants) {
                foreach ($product->variantPrices as $vp) {
                    $variantPrice = (float)($vp->price > 0 ? $vp->price : ($product->new_price ?? 0));
                    $oldPrice     = (float)($product->old_price ?? 0);
                    $inStock      = ($vp->stock > 0 || ($vp->stock === null && $product->stock > 0));
                    $sizeName     = $vp->size?->sizeName ?: '';
                    $colorName    = $vp->color?->colorName ?: '';
                    $variantTitle = $product->name . ($sizeName ? " - {$sizeName}" : '') . ($colorName ? " - {$colorName}" : '');

                    $xml .= '<item>' . "\n";
                    $xml .= '<g:id>' . htmlspecialchars($product->id . '_' . $vp->id, ENT_XML1) . '</g:id>' . "\n";
                    $xml .= '<g:item_group_id>' . htmlspecialchars((string)$product->id, ENT_XML1) . '</g:item_group_id>' . "\n";
                    $xml .= '<title>' . htmlspecialchars($variantTitle, ENT_XML1) . '</title>' . "\n";
                    $xml .= '<description>' . htmlspecialchars($cleanDesc, ENT_XML1) . '</description>' . "\n";
                    $xml .= '<link>' . htmlspecialchars($prodLink, ENT_XML1) . '</link>' . "\n";
                    $xml .= '<g:image_link>' . htmlspecialchars($imageUrl, ENT_XML1) . '</g:image_link>' . "\n";
                    $xml .= '<g:brand>' . htmlspecialchars($brandName, ENT_XML1) . '</g:brand>' . "\n";
                    $xml .= '<g:condition>new</g:condition>' . "\n";
                    $xml .= '<g:availability>' . ($inStock ? 'in stock' : 'out of stock') . '</g:availability>' . "\n";
                    if ($oldPrice > $variantPrice && $variantPrice > 0) {
                        $xml .= '<g:price>' . round($oldPrice) . ' BDT</g:price>' . "\n";
                        $xml .= '<g:sale_price>' . round($variantPrice) . ' BDT</g:sale_price>' . "\n";
                    } else {
                        $xml .= '<g:price>' . round($variantPrice) . ' BDT</g:price>' . "\n";
                    }
                    if ($sizeName) $xml .= '<g:size>' . htmlspecialchars($sizeName, ENT_XML1) . '</g:size>' . "\n";
                    if ($colorName) $xml .= '<g:color>' . htmlspecialchars($colorName, ENT_XML1) . '</g:color>' . "\n";
                    $xml .= '<g:product_type>' . htmlspecialchars($catName, ENT_XML1) . '</g:product_type>' . "\n";
                    $xml .= '</item>' . "\n";
                }
            } else {
                $price    = (float)($product->new_price ?? 0);
                $oldPrice = (float)($product->old_price ?? 0);
                $inStock  = ($product->stock > 0);

                $xml .= '<item>' . "\n";
                $xml .= '<g:id>' . htmlspecialchars((string)$product->id, ENT_XML1) . '</g:id>' . "\n";
                $xml .= '<g:item_group_id>' . htmlspecialchars((string)$product->id, ENT_XML1) . '</g:item_group_id>' . "\n";
                $xml .= '<title>' . htmlspecialchars($product->name, ENT_XML1) . '</title>' . "\n";
                $xml .= '<description>' . htmlspecialchars($cleanDesc, ENT_XML1) . '</description>' . "\n";
                $xml .= '<link>' . htmlspecialchars($prodLink, ENT_XML1) . '</link>' . "\n";
                $xml .= '<g:image_link>' . htmlspecialchars($imageUrl, ENT_XML1) . '</g:image_link>' . "\n";
                $xml .= '<g:brand>' . htmlspecialchars($brandName, ENT_XML1) . '</g:brand>' . "\n";
                $xml .= '<g:condition>new</g:condition>' . "\n";
                $xml .= '<g:availability>' . ($inStock ? 'in stock' : 'out of stock') . '</g:availability>' . "\n";
                if ($oldPrice > $price && $price > 0) {
                    $xml .= '<g:price>' . round($oldPrice) . ' BDT</g:price>' . "\n";
                    $xml .= '<g:sale_price>' . round($price) . ' BDT</g:sale_price>' . "\n";
                } else {
                    $xml .= '<g:price>' . round($price > 0 ? $price : $oldPrice) . ' BDT</g:price>' . "\n";
                }
                if ($product->pro_unit) {
                    $xml .= '<g:size>' . htmlspecialchars($product->pro_unit, ENT_XML1) . '</g:size>' . "\n";
                }
                $xml .= '<g:product_type>' . htmlspecialchars($catName, ENT_XML1) . '</g:product_type>' . "\n";
                $xml .= '</item>' . "\n";
            }
        }

        $xml .= '</channel>' . "\n";
        $xml .= '</rss>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Download the CSV file as an attachment.
     */
    public function download()
    {
        $response = $this->feed();
        $response->headers->set('Content-Disposition', 'attachment; filename="facebook_catalog_feed.csv"');
        return $response;
    }

    /**
     * Write single product or variant rows into the CSV stream.
     */
    protected function writeProductRows($handle, Product $product, string $siteName)
    {
        // 1. Clean Description
        $rawDescription = $product->description ?: $product->name;
        $cleanDescription = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($rawDescription, ENT_QUOTES, 'UTF-8'))));
        if (empty($cleanDescription)) {
            $cleanDescription = $product->name;
        }

        $condition = 'new';
        $sku       = $product->product_code ?: (string)$product->id;

        // URLs
        $productLink = route('product', $product->slug);

        // Featured Image
        $imagePath = $product->image?->image ?? '';
        $imageUrl  = $this->resolveImageUrl($imagePath);

        // Additional Images
        $additionalImageUrls = [];
        if ($product->images && $product->images->count() > 0) {
            foreach ($product->images->take(10) as $extraImage) {
                if (!empty($extraImage->image) && $extraImage->image !== $imagePath) {
                    $additionalImageUrls[] = $this->resolveImageUrl($extraImage->image);
                }
            }
        }
        $additionalImagesStr = implode(',', array_filter($additionalImageUrls));

        // Brand & Category
        $brandName    = $product->brand?->name ?: $siteName;
        $categoryName = $product->category?->name ?: 'General';

        // Check if product has modern variantPrices
        $variantPrices = $product->variantPrices;

        if ($variantPrices && $variantPrices->count() > 0) {
            foreach ($variantPrices as $vp) {
                $variantPrice = (float)($vp->price > 0 ? $vp->price : ($product->new_price ?? 0));
                $oldPrice     = (float)($product->old_price ?? 0);

                if ($oldPrice > $variantPrice && $variantPrice > 0) {
                    $priceStr     = round($oldPrice) . ' BDT';
                    $salePriceStr = round($variantPrice) . ' BDT';
                } else {
                    $priceStr     = round($variantPrice) . ' BDT';
                    $salePriceStr = round($variantPrice) . ' BDT';
                }

                $inStock      = ($vp->stock > 0 || ($vp->stock === null && $product->stock > 0));
                $availability = $inStock ? 'in stock' : 'out of stock';
                $sizeName     = $vp->size?->sizeName ?: '';
                $colorName    = $vp->color?->colorName ?: '';
                $title        = $product->name . ($sizeName ? " - {$sizeName}" : '') . ($colorName ? " - {$colorName}" : '');

                fputcsv($handle, [
                    'id'                      => $product->id . '_' . $vp->id,
                    'title'                   => $title,
                    'item_group_id'           => (string)$product->id,
                    'description'             => $cleanDescription,
                    'availability'            => $availability,
                    'condition'               => $condition,
                    'sku'                     => $sku . '-' . $vp->id,
                    'price'                   => $priceStr,
                    'sale_price'              => $salePriceStr,
                    'link'                    => $productLink,
                    'image_link'              => $imageUrl,
                    'brand'                   => $brandName,
                    'google_product_category' => $categoryName,
                    'fb_product_category'     => $categoryName,
                    'size'                    => $sizeName,
                    'color'                   => $colorName,
                    'additional_image_link'   => $additionalImagesStr,
                ]);
            }
        } else {
            // Standalone Product
            $oldPrice = (float)($product->old_price ?? 0);
            $newPrice = (float)($product->new_price ?? 0);

            if ($oldPrice > $newPrice && $newPrice > 0) {
                $priceStr     = round($oldPrice) . ' BDT';
                $salePriceStr = round($newPrice) . ' BDT';
            } else {
                $finalPrice   = $newPrice > 0 ? $newPrice : $oldPrice;
                $priceStr     = round($finalPrice) . ' BDT';
                $salePriceStr = round($finalPrice) . ' BDT';
            }

            $availability = ($product->stock > 0) ? 'in stock' : 'out of stock';

            fputcsv($handle, [
                'id'                      => (string)$product->id,
                'title'                   => $product->name,
                'item_group_id'           => (string)$product->id,
                'description'             => $cleanDescription,
                'availability'            => $availability,
                'condition'               => $condition,
                'sku'                     => $sku,
                'price'                   => $priceStr,
                'sale_price'              => $salePriceStr,
                'link'                    => $productLink,
                'image_link'              => $imageUrl,
                'brand'                   => $brandName,
                'google_product_category' => $categoryName,
                'fb_product_category'     => $categoryName,
                'size'                    => $product->pro_unit ?? '',
                'color'                   => '',
                'additional_image_link'   => $additionalImagesStr,
            ]);
        }
    }

    /**
     * Resolve absolute image URL from relative database path.
     */
    protected function resolveImageUrl(string $imagePath): string
    {
        if (empty($imagePath)) {
            return asset('public/frontEnd/images/placeholder.png');
        }

        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return $imagePath;
        }

        return url('/') . '/' . ltrim($imagePath, '/');
    }
}
