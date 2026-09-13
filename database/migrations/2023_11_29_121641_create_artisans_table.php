<?php

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
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
        Schema::create('artisans', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('profile_photo');
            $table->string('cover_photo');
            $table->foreignIdFor(User::class);
            $table->string('experience_in_year');
            $table->string('website')->nullable();
            $table->string('last_education');
            $table->string('date_of_birth');
            $table->string('profession');
            $table->foreignIdFor(Country::class);
            $table->foreignIdFor(State::class);
            $table->foreignIdFor(City::class);
            $table->text('address');
            $table->longText('biography');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisans');
    }
};
