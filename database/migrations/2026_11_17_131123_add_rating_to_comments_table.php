<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable();
        });

        // Existing seeded comments have no rating yet. Give them a
        // deterministic 4 or 5 star value so older reviews still display.
        DB::table('comments')->whereNull('rating')->update(['rating' => DB::raw('4 + (id % 2)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};
