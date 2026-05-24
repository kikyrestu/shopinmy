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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->boolean('is_new_user_only')->default(false)->after('is_public');
            $table->foreignId('target_user_id')->nullable()->after('is_new_user_only')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['target_user_id']);
            $table->dropColumn(['is_new_user_only', 'target_user_id']);
        });
    }
};
