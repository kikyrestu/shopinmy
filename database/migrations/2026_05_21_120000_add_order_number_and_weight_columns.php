<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add order_number to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable()->after('id');
        });

        // Migrate existing tracking_no values that look like order numbers (ORD-XXXX) to order_number
        DB::table('orders')
            ->where('tracking_no', 'LIKE', 'ORD-%')
            ->update([
                'order_number' => DB::raw('tracking_no'),
                'tracking_no' => null,
            ]);

        // Add weight to products table
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('weight', 8, 3)->default(0.500)->after('price')->comment('Weight in KG');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
