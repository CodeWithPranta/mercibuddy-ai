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
            $table->json('skills')->nullable()->after('profession_type');
        });

        $defaultSkills = [
            'beauty-and-wellness' => ['Beauty treatments', 'Client care', 'Appointment scheduling'],
            'childcare' => ['Child supervision', 'Activity planning', 'First aid'],
            'cleaning' => ['Deep cleaning', 'Sanitising', 'Laundry'],
            'elderly-and-disability-care' => ['Personal care', 'Mobility support', 'Companionship'],
            'fitness-and-yoga' => ['Workout planning', 'Yoga instruction', 'Nutrition guidance'],
            'plumbing-and-handyman' => ['Pipe repair', 'Leak detection', 'Installation'],
            'tutoring-and-education' => ['Lesson planning', 'Exam preparation', 'Student mentoring'],
            'cooking-and-catering' => ['Meal planning', 'Food preparation', 'Event catering'],
            'electrical-services' => ['Wiring', 'Circuit repair', 'Safety inspection'],
            'driving-and-transport' => ['Safe driving', 'Route planning', 'Vehicle checks'],
        ];

        $categorySlugs = DB::table('categories')->pluck('slug', 'id');

        DB::table('artisans')
            ->whereNull('skills')
            ->orderBy('id')
            ->get(['id', 'category_id', 'profession'])
            ->each(function (object $artisan) use ($categorySlugs, $defaultSkills): void {
                $skills = $defaultSkills[$categorySlugs[$artisan->category_id] ?? ''] ?? [
                    $artisan->profession,
                    'Quality work',
                    'Customer communication',
                ];

                DB::table('artisans')
                    ->where('id', $artisan->id)
                    ->update(['skills' => json_encode(array_values(array_unique($skills)))]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artisans', function (Blueprint $table) {
            $table->dropColumn('skills');
        });
    }
};
