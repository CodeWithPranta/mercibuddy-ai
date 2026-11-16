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
        Schema::table('artisan_user', function (Blueprint $table) {
            $table->dropForeign(['artisan_id']);
            $table->foreign('artisan_id')
                ->references('id')
                ->on('artisans')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artisan_user', function (Blueprint $table) {
            $table->dropForeign(['artisan_id']);
            $table->foreign('artisan_id')
                ->references('id')
                ->on('artisans');
        });
    }
};
