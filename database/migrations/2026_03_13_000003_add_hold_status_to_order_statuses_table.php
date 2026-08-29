<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\OrderStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $exists = OrderStatus::where('slug', 'hold')->exists();
        if (!$exists) {
            OrderStatus::create([
                'name'   => 'Hold',
                'slug'   => 'hold',
                'status' => 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        OrderStatus::where('slug', 'hold')->delete();
    }
};
