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
            $table->string('guest_address')->nullable()->after('guest_phone');
            $table->string('guest_city')->nullable()->after('guest_address');
            $table->string('guest_state')->nullable()->after('guest_city');
            $table->string('guest_postcode')->nullable()->after('guest_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['guest_address', 'guest_city', 'guest_state', 'guest_postcode']);
        });
    }
};
