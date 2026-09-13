<?php

use App\Livewire\FilterSection;
use App\Models\Artisan;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function seedRouteDirectory(): array
{
    $cleaning = Category::create(['name' => 'Cleaning', 'slug' => 'cleaning', 'icon' => 'x']);
    $tutoring = Category::create(['name' => 'Tutoring and Education', 'slug' => 'tutoring-and-education', 'icon' => 'x']);

    $bangladesh = Country::create(['shortname' => 'BD', 'name' => 'Bangladesh', 'slug' => 'bangladesh', 'phonecode' => '+880']);
    $dhakaState = State::create(['name' => 'Dhaka', 'country_id' => $bangladesh->id]);
    $dhaka = City::create(['name' => 'Dhaka', 'state_id' => $dhakaState->id]);

    $france = Country::create(['shortname' => 'FR', 'name' => 'France', 'slug' => 'france', 'phonecode' => '+33']);
    $brittany = State::create(['name' => 'Brittany', 'country_id' => $france->id]);
    $rennes = City::create(['name' => 'Rennes', 'state_id' => $brittany->id]);

    $makeArtisan = function (string $name, string $email, Category $category, Country $country, State $state, City $city, string $experience, string $createdAt): Artisan {
        $user = User::create(['name' => $name, 'email' => $email, 'password' => 'password', 'user_type' => 2]);

        $artisan = Artisan::create([
            'full_name' => $name,
            'profile_photo' => 'photo.jpg',
            'cover_photo' => 'cover.jpg',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'experience_in_year' => $experience,
            'last_education' => 'Diploma',
            'date_of_birth' => '1990-01-01',
            'profession' => 'Cleaner',
            'country_id' => $country->id,
            'state_id' => $state->id,
            'city_id' => $city->id,
            'address' => '1 Test Street',
            'biography' => 'Bio',
        ]);
        $artisan->created_at = $createdAt;
        $artisan->save();

        return $artisan;
    };

    // Same country + category, different likes/experience/age.
    $liked = $makeArtisan('Liked Artisan', 'liked@test.com', $cleaning, $bangladesh, $dhakaState, $dhaka, '3', '2024-06-01 10:00:00');
    $veteran = $makeArtisan('Veteran Artisan', 'veteran@test.com', $cleaning, $bangladesh, $dhakaState, $dhaka, '10', '2023-01-01 10:00:00');
    $rookie = $makeArtisan('Rookie Artisan', 'rookie@test.com', $cleaning, $bangladesh, $dhakaState, $dhaka, '1', '2025-01-01 10:00:00');
    $french = $makeArtisan('French Artisan', 'french@test.com', $cleaning, $france, $brittany, $rennes, '8', '2023-06-01 10:00:00');
    $tutor = $makeArtisan('Dhaka Tutor', 'tutor@test.com', $tutoring, $bangladesh, $dhakaState, $dhaka, '6', '2024-01-01 10:00:00');

    $like = function (Artisan $artisan, int $count, int $likersOffset = 100): void {
        foreach (range(1, $count) as $i) {
            $voter = User::create([
                'name' => "Voter {$artisan->id}-{$i}",
                'email' => "voter-{$artisan->id}-{$i}-{$likersOffset}@test.com",
                'password' => 'password',
                'user_type' => 0,
            ]);
            DB::table('artisan_user')->insert([
                'user_id' => $voter->id,
                'artisan_id' => $artisan->id,
                'reaction' => 'like',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    };

    $like($liked, 5);
    $like($veteran, 1);
    $like($french, 9);

    return compact('cleaning', 'tutoring', 'bangladesh', 'france', 'liked', 'veteran', 'rookie', 'french', 'tutor');
}

function verifiedUser(): User
{
    return User::create([
        'name' => 'Verified User',
        'email' => 'verified@test.com',
        'password' => 'password',
        'user_type' => 0,
        'email_verified_at' => now(),
    ]);
}

test('listing shows matching artisans ranked by likes, experience, then seniority', function () {
    $seeded = seedRouteDirectory();

    $response = $this->get('/artisans/bangladesh/cleaning');

    $response->assertOk()
        ->assertSeeInOrder(['Liked Artisan', 'Veteran Artisan', 'Rookie Artisan'])
        ->assertDontSee('French Artisan')
        ->assertDontSee('Dhaka Tutor');
});

test('listing can be sorted by experience or newest', function () {
    seedRouteDirectory();

    $this->get('/artisans/bangladesh/cleaning?sort=experienced')
        ->assertOk()
        ->assertSeeInOrder(['Veteran Artisan', 'Liked Artisan', 'Rookie Artisan']);

    $this->get('/artisans/bangladesh/cleaning?sort=newest')
        ->assertOk()
        ->assertSeeInOrder(['Rookie Artisan', 'Liked Artisan', 'Veteran Artisan']);
});

test('clicking a category after selecting a country opens the listing', function () {
    $seeded = seedRouteDirectory();

    Livewire::test(FilterSection::class)
        ->set('selectedCountry', $seeded['bangladesh']->id)
        ->call('updateSelectedCategory', $seeded['cleaning']->id)
        ->assertRedirect('/artisans/bangladesh/cleaning');
});

test('clicking a category without a country shows an error instead', function () {
    $seeded = seedRouteDirectory();

    Livewire::test(FilterSection::class)
        ->call('updateSelectedCategory', $seeded['cleaning']->id)
        ->assertHasErrors(['selectedCountry'])
        ->assertNoRedirect();
});

test('listing rejects unknown country or category slugs', function () {
    seedRouteDirectory();

    $this->get('/artisans/neverland/cleaning')->assertNotFound();
    $this->get('/artisans/bangladesh/nevertrade')->assertNotFound();
});

test('legacy listing url redirects to the dynamic url', function () {
    $seeded = seedRouteDirectory();

    $this->withSession(['filter_values' => [
        'country_id' => $seeded['bangladesh']->id,
        'state_id' => null,
        'city_id' => null,
        'category_id' => $seeded['cleaning']->id,
    ]])->get('/filtered-artisans')->assertRedirect('/artisans/bangladesh/cleaning');
});

test('legacy listing url without a selection goes home', function () {
    $this->get('/filtered-artisans')->assertRedirect('/');
});

test('profile renders on its canonical url for verified users', function () {
    $seeded = seedRouteDirectory();

    $this->actingAs(verifiedUser())
        ->get('/artisan/'.$seeded['liked']->id.'/liked-artisan')
        ->assertOk()
        ->assertSee('Liked Artisan');
});

test('profile requires authentication', function () {
    $seeded = seedRouteDirectory();

    $this->get('/artisan/'.$seeded['liked']->id.'/liked-artisan')->assertRedirect('/login');
});

test('legacy profile url redirects to the canonical url', function () {
    $seeded = seedRouteDirectory();

    $this->actingAs(verifiedUser())
        ->get('/artisan-detail/'.$seeded['liked']->id)
        ->assertRedirect('/artisan/'.$seeded['liked']->id.'/liked-artisan');
});

test('profile with a wrong slug redirects to the canonical url', function () {
    $seeded = seedRouteDirectory();

    $this->actingAs(verifiedUser())
        ->get('/artisan/'.$seeded['liked']->id.'/wrong-slug')
        ->assertRedirect('/artisan/'.$seeded['liked']->id.'/liked-artisan');
});
