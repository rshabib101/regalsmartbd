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

        return view('backEnd.settings.facebook_catalog', compact(
            'totalProducts',
            'inStockProducts',
            'totalCategories',
            'feedUrl'
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
                'fb_product_category',
                'size',
                'additional_image_link',
            ]);

            $siteName = GeneralSetting::first()?->name ?? config('app.name', 'Organic Shop');

            // Chunk through products to be memory efficient
            Product::where('status', 1)
                ->where('in_facebook_catalog', 1)
                ->with(['image', 'images', 'category', 'brand', 'sizes'])
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

        // 2. Pricing & Currency
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

        // 3. Availability & Condition
        $availability = ($product->stock > 0) ? 'in stock' : 'out of stock';
        $condition    = 'new';
        $sku          = $product->product_code ?: (string)$product->id;

        // 4. URLs
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

        // 5. Brand & Category
        $brandName    = $product->brand?->name ?: $siteName;
        $categoryName = $product->category?->name ?: 'General';

        // 6. Sizes / Variants
        $sizes = $product->sizes;

        if ($sizes && $sizes->count() > 0) {
            // Write each size variant with common item_group_id
            foreach ($sizes as $size) {
                fputcsv($handle, [
                    'id'                    => $product->id . '_' . $size->id,
                    'title'                 => $product->name,
                    'item_group_id'         => (string)$product->id,
                    'description'           => $cleanDescription,
                    'availability'          => $availability,
                    'condition'             => $condition,
                    'sku'                   => $sku . '-' . $size->id,
                    'price'                 => $priceStr,
                    'sale_price'            => $salePriceStr,
                    'link'                  => $productLink,
                    'image_link'            => $imageUrl,
                    'brand'                 => $brandName,
                    'fb_product_category'   => $categoryName,
                    'size'                  => $size->sizeName,
                    'additional_image_link' => $additionalImagesStr,
                ]);
            }
        } else {
            // Standalone Product
            fputcsv($handle, [
                'id'                    => (string)$product->id,
                'title'                 => $product->name,
                'item_group_id'         => (string)$product->id,
                'description'           => $cleanDescription,
                'availability'          => $availability,
                'condition'             => $condition,
                'sku'                   => $sku,
                'price'                 => $priceStr,
                'sale_price'            => $salePriceStr,
                'link'                  => $productLink,
                'image_link'            => $imageUrl,
                'brand'                 => $brandName,
                'fb_product_category'   => $categoryName,
                'size'                  => '',
                'additional_image_link' => $additionalImagesStr,
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
