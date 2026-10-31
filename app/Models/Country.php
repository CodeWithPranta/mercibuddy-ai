<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    public const FEATURED_ORDER = [
        'Switzerland',
        'India',
        'United Kingdom',
        'United States',
        'Canada',
        'Bangladesh',
        'Australia',
        'France',
    ];

    protected $fillable = ['shortname', 'name', 'slug', 'phonecode'];

    /**
     * @return Collection<int, Country>
     */
    public static function ordered(): Collection
    {
        $priority = array_flip(self::FEATURED_ORDER);

        return static::query()
            ->get()
            ->sort(function (Country $first, Country $second) use ($priority): int {
                $firstPriority = $priority[$first->name] ?? PHP_INT_MAX;
                $secondPriority = $priority[$second->name] ?? PHP_INT_MAX;

                if ($firstPriority === $secondPriority) {
                    return strcasecmp($first->name, $second->name);
                }

                return $firstPriority <=> $secondPriority;
            })
            ->values();
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function artisans(): HasMany
    {
        return $this->hasMany(Artisan::class);
    }
}
