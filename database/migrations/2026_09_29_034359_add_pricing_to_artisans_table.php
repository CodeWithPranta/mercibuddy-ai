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
        Schema::table('artisans', function (Blueprint $table) {
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('profession_type');
            $table->string('currency', 3)->default('EUR')->after('hourly_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artisans', function (Blueprint $table) {
            $table->dropColumn(['hourly_rate', 'currency']);
        });
    }
};
