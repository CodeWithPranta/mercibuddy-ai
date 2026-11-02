<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['shortname', 'name', 'slug', 'phonecode'];

    /**
     * @return Collection<int, Country>
     */
    public static function ordered(): Collection
    {
        return static::query()
            ->orderBy('name')
            ->get();
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
