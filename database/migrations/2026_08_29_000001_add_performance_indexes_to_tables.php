<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Indexes on products table
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'approval_status'], 'products_status_approval_idx');
            $table->index('category_id', 'products_category_id_idx');
            $table->index('brand_id', 'products_brand_id_idx');
            $table->index('slug', 'products_slug_idx');
            $table->index('topsale', 'products_topsale_idx');
            if (Schema::hasColumn('products', 'flashsale')) {
                $table->index('flashsale', 'products_flashsale_idx');
            }
        });

        // Indexes on orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->index('customer_id', 'orders_customer_id_idx');
            $table->index('order_status', 'orders_order_status_idx');
            $table->index('invoice_id', 'orders_invoice_id_idx');
            $table->index('created_at', 'orders_created_at_idx');
        });

        // Indexes on categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->index('parent_id', 'categories_parent_id_idx');
            $table->index('status', 'categories_status_idx');
            $table->index('slug', 'categories_slug_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_approval_idx');
            $table->dropIndex('products_category_id_idx');
            $table->dropIndex('products_brand_id_idx');
            $table->dropIndex('products_slug_idx');
            $table->dropIndex('products_topsale_idx');
            if (Schema::hasColumn('products', 'flashsale')) {
                $table->dropIndex('products_flashsale_idx');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_id_idx');
            $table->dropIndex('orders_order_status_idx');
            $table->dropIndex('orders_invoice_id_idx');
            $table->dropIndex('orders_created_at_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_parent_id_idx');
            $table->dropIndex('categories_status_idx');
            $table->dropIndex('categories_slug_idx');
        });
    }
};
