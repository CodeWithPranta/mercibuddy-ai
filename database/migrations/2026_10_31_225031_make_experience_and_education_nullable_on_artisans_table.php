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
        Schema::table('artisans', function (Blueprint $table) {
            $table->string('experience_in_year')->nullable()->change();
            $table->string('last_education')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('artisans')->whereNull('experience_in_year')->update(['experience_in_year' => '0']);
        DB::table('artisans')->whereNull('last_education')->update(['last_education' => '']);

        Schema::table('artisans', function (Blueprint $table) {
            $table->string('experience_in_year')->nullable(false)->change();
            $table->string('last_education')->nullable(false)->change();
        });
    }
};
