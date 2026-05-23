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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('reference');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('awb_label_url')->nullable()->after('tracking_no');
            $table->unique('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_number']);
            $table->dropColumn('awb_label_url');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });
    }
};
