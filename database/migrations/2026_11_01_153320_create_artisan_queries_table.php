<?php

use App\Models\Artisan;
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
        Schema::create('artisan_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Artisan::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 30);
            $table->text('query');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['artisan_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisan_queries');
    }
};
