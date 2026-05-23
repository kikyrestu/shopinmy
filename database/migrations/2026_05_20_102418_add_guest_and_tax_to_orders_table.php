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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->after('address_id')->constrained()->nullOnDelete();
            $table->string('guest_email')->nullable()->after('tracking_no');
            $table->string('guest_name')->nullable()->after('guest_email');
            $table->string('guest_phone')->nullable()->after('guest_name');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('shipping_cost');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
            $table->dropColumn(['voucher_id', 'guest_email', 'guest_name', 'guest_phone', 'tax_rate', 'tax_amount']);
        });
    }
};
