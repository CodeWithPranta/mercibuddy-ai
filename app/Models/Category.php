<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'professions'];

    protected function casts(): array
    {
        return [
            'professions' => 'array',
        ];
    }

    public function artisans(): HasMany
    {
        return $this->hasMany(Artisan::class);
    }

    /**
     * @return array<string, string>
     */
    public static function professionOptions(?int $categoryId): array
    {
        $category = $categoryId ? static::find($categoryId) : null;

        if (! $category) {
            return [];
        }

        $stored = $category->professions ?? [];

        $configured = config('professions.'.$category->slug, []);

        $used = $category->artisans()
            ->whereNotNull('profession')
            ->distinct()
            ->orderBy('profession')
            ->pluck('profession')
            ->all();

        return collect($stored)
            ->merge($configured)
            ->merge($used)
            ->filter()
            ->unique()
            ->mapWithKeys(fn (string $profession): array => [$profession => $profession])
            ->all();
    }
}
