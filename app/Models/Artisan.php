<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Artisan extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'category_id',
        'profile_photo',
        'cover_photo',
        'user_id',
        'experience_in_year',
        'website',
        'last_education',
        'date_of_birth',
        'profession',
        'profession_type',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'biography',
        'video_cv',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function contactDetail(): HasOne
    {
        return $this->hasOne(ContactDetail::class, 'user_id', 'user_id');
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'artisan_user')->wherePivot('reaction', '=', 'like');
    }

    public function dislikers()
    {
        return $this->belongsToMany(User::class, 'artisan_user')->wherePivot('reaction', '=', 'dislike');
    }

    public function likeCount(): int
    {
        return (int) ($this->getAttribute('likers_count') ?? $this->likers()->count());
    }

    public function dislikeCount(): int
    {
        return (int) ($this->getAttribute('dislikers_count') ?? $this->dislikers()->count());
    }

    public function satisfactionPercentage(): int
    {
        $total = $this->likeCount() + $this->dislikeCount();

        return $total === 0 ? 0 : (int) round($this->likeCount() / $total * 100);
    }

    public function experienceYears(): int
    {
        return (int) $this->experience_in_year;
    }

    public function locationLabel(): string
    {
        return collect([$this->city?->name, $this->state?->name, $this->country?->name])
            ->filter()
            ->implode(', ');
    }
}
