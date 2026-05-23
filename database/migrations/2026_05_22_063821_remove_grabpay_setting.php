<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->where('key', 'grabpay_enabled')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->insert([
            'key' => 'grabpay_enabled',
            'value' => false,
            'group' => 'payment',
            'is_encrypted' => false
        ]);
    }
};
