<?php

namespace App\Ai\Tools;

use App\Models\Artisan;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class FindMatchingArtisans implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the verified MerciBuddy artisan directory using the user request. Returns real artisan records only, including their IDs, names, professions, locations, experience, and profile links.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $search = trim((string) ($request['search'] ?? ''));
        $terms = collect(preg_split('/\s+/', strtolower($search), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn (string $term): string => trim((string) preg_replace('/[^a-z0-9-]/', '', $term), '-'))
            ->reject(fn (string $term): bool => $term === '' || in_array($term, [
                'a', 'an', 'and', 'anyone', 'around', 'artisan', 'artisans', 'are', 'area', 'at',
                'be', 'been', 'book', 'booking', 'can', 'cities', 'city', 'could', 'countries', 'country', 'did', 'do', 'does',
                'find', 'for', 'from', 'get', 'getting', 'good', 'hello', 'help', 'hey', 'hi',
                'hire', 'hiring', 'here', 'how', 'i', 'im', "i'm", 'in', 'is', 'it', 'its',
                'local', 'look', 'looking', 'me', 'my', 'near', 'need', 'needs', 'of', 'or',
                'our', 'please', 'professional', 'professionals', 'province', 'region', 'search', 'searching', 'service',
                'services', 'skilled', 'somebody', 'someone', 'state', 'states', 'that', 'the', 'there', 'this',
                'to', 'town', 'trusted', 'us', 'want', 'wants', 'was', 'we', 'were', 'what',
                'which', 'who', 'with', 'you', 'your',
            ], true))
            ->map(fn (string $term): string => match ($term) {
                'cleaner', 'cleaners', 'housekeeper', 'housekeepers', 'maid', 'maids', 'mopping' => 'cleaning',
                'nanny', 'nannies', 'babysitter', 'babysitters', 'babysitting' => 'childcare',
                'caregiver', 'caregivers', 'carer', 'carers' => 'elderly',
                'trainer', 'trainers', 'coach', 'coaches', 'gym', 'yoga' => 'fitness',
                'beautician', 'beauticians', 'spa', 'salon', 'salons' => 'beauty',
                'plumber', 'plumbers', 'plumbing', 'tap', 'taps', 'leak', 'leaks', 'leaking', 'fix', 'fixing', 'handyman' => 'plumb',
                'tutor', 'tutors', 'tutoring', 'tuition', 'science', 'mathematics', 'physics', 'chemistry', 'biology' => 'tutor',
                'instructor', 'instructors' => 'instructor',
                'cook', 'cooks', 'cooking', 'chef', 'chefs', 'catering', 'kitchen', 'meal', 'meals' => 'cook',
                'electrician', 'electricians', 'electrical', 'electric', 'wiring', 'appliance', 'appliances' => 'electric',
                'driver', 'drivers', 'driving', 'taxi', 'taxis', 'transport' => 'driv',
                default => $term,
            })
            ->filter()
            ->unique()
            ->values();

        $artisans = Artisan::query()
            ->with(['category', 'country', 'state', 'city', 'contactDetail'])
            ->when($terms->isNotEmpty(), function ($query) use ($terms): void {
                $terms->each(function (string $term) use ($query): void {
                    $like = '%'.$term.'%';

                    $query->where(function ($termQuery) use ($like): void {
                        $termQuery
                            ->where('full_name', 'like', $like)
                            ->orWhere('profession', 'like', $like)
                            ->orWhere('profession_type', 'like', $like)
                            ->orWhere('address', 'like', $like)
                            ->orWhere('biography', 'like', $like)
                            ->orWhereHas('category', fn ($relationQuery) => $relationQuery->where('name', 'like', $like))
                            ->orWhereHas('country', fn ($relationQuery) => $relationQuery->where('name', 'like', $like))
                            ->orWhereHas('state', fn ($relationQuery) => $relationQuery->where('name', 'like', $like))
                            ->orWhereHas('city', fn ($relationQuery) => $relationQuery->where('name', 'like', $like));
                    });
                });
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Artisan $artisan): array => [
                'id' => $artisan->id,
                'name' => $artisan->full_name,
                'profession' => $artisan->profession,
                'category' => $artisan->category?->name,
                'experience_years' => $artisan->experience_in_year,
                'profession_type' => $artisan->profession_type,
                'country' => $artisan->country?->name,
                'state' => $artisan->state?->name,
                'city' => $artisan->city?->name,
                'address' => $artisan->address,
                'education' => $artisan->last_education,
                'date_of_birth' => $artisan->date_of_birth,
                'biography' => $artisan->biography,
                'website' => $artisan->website,
                'video_cv' => $artisan->video_cv,
                'profile_photo' => $artisan->profile_photo,
                'cover_photo' => $artisan->cover_photo,
                'contact' => [
                    'phone' => $artisan->contactDetail?->phone,
                    'email' => $artisan->contactDetail?->email,
                    'facebook' => $artisan->contactDetail?->facebook,
                    'linkedin' => $artisan->contactDetail?->linkedin,
                    'whatsapp' => $artisan->contactDetail?->whatsapp,
                ],
                'profile_url' => route('artisan.show', [
                    'artisan' => $artisan->id,
                    'slug' => Str::slug($artisan->full_name),
                ]),
            ]);

        return $artisans->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()
                ->description('The user\'s complete natural-language artisan request, including any category, city, state, or country they mentioned.')
                ->required(),
        ];
    }
}
