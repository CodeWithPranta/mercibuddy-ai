<?php

use App\Models\Category;
use App\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('countries', 'slug')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->string('slug')->nullable()->unique()->after('name');
            });
        }

        Country::whereNull('slug')->orWhere('slug', '')->each(function (Country $country): void {
            $slug = Str::slug($country->name);
            $suffix = 1;

            while (Country::where('slug', $slug)->where('id', '!=', $country->id)->exists()) {
                $suffix++;
                $slug = Str::slug($country->name).'-'.$suffix;
            }

            $country->update(['slug' => $slug]);
        });

        Category::whereNull('slug')->orWhere('slug', '')->each(function (Category $category): void {
            $category->update(['slug' => Str::slug($category->name)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
