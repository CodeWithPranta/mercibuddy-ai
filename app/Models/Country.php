<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['shortname', 'name', 'slug', 'phonecode'];

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function artisans(): HasMany
    {
        return $this->hasMany(Artisan::class);
    }
}
