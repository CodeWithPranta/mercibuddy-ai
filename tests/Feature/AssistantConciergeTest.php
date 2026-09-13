<?php

use App\Ai\LocalConcierge;
use App\Models\Artisan;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;

function seedConciergeDirectory(): array
{
    $fitness = Category::create(['name' => 'Fitness and Yoga', 'slug' => 'fitness-and-yoga', 'icon' => 'x']);
    $plumbing = Category::create(['name' => 'Plumbing and Handyman', 'slug' => 'plumbing-and-handyman', 'icon' => 'x']);
    $tutoring = Category::create(['name' => 'Tutoring and Education', 'slug' => 'tutoring-and-education', 'icon' => 'x']);
    $cooking = Category::create(['name' => 'Cooking and Catering', 'slug' => 'cooking-and-catering', 'icon' => 'x']);
    $electrical = Category::create(['name' => 'Electrical Services', 'slug' => 'electrical-services', 'icon' => 'x']);

    $switzerland = Country::create(['shortname' => 'CH', 'name' => 'Switzerland', 'phonecode' => '+41']);
    $baselStadt = State::create(['name' => 'Basel-Stadt', 'country_id' => $switzerland->id]);
    $basel = City::create(['name' => 'Basel', 'state_id' => $baselStadt->id]);

    $france = Country::create(['shortname' => 'FR', 'name' => 'France', 'phonecode' => '+33']);
    $brittany = State::create(['name' => 'Brittany', 'country_id' => $france->id]);
    $rennes = City::create(['name' => 'Rennes', 'state_id' => $brittany->id]);

    $bangladesh = Country::create(['shortname' => 'BD', 'name' => 'Bangladesh', 'phonecode' => '+880']);
    $khulnaDivision = State::create(['name' => 'Khulna', 'country_id' => $bangladesh->id]);
    $khulna = City::create(['name' => 'Khulna', 'state_id' => $khulnaDivision->id]);

    $makeArtisan = function (string $name, string $email, Category $category, string $profession, Country $country, State $state, City $city, string $biography): Artisan {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'user_type' => 2,
        ]);

        return Artisan::create([
            'full_name' => $name,
            'profile_photo' => 'photo.jpg',
            'cover_photo' => 'cover.jpg',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'experience_in_year' => '5',
            'last_education' => 'Diploma',
            'date_of_birth' => '1990-01-01',
            'profession' => $profession,
            'country_id' => $country->id,
            'state_id' => $state->id,
            'city_id' => $city->id,
            'address' => '1 Test Street, '.$city->name,
            'biography' => $biography,
        ]);
    };

    $luca = $makeArtisan('Luca Meyer', 'luca@test.com', $fitness, 'Fitness Instructor', $switzerland, $baselStadt, $basel, 'Certified fitness instructor in Basel offering personal training and gym coaching.');
    $sophie = $makeArtisan('Sophie Martin', 'sophie@test.com', $plumbing, 'Plumber', $france, $brittany, $rennes, 'Rennes plumber. I fix leaking taps, repair leaks and install taps.');
    $anna = $makeArtisan('Anna Keller', 'anna@test.com', $tutoring, 'Science Tutor', $switzerland, $baselStadt, $basel, 'Science tutor in Basel for physics, chemistry and biology tutoring.');
    $karim = $makeArtisan('Karim Sheikh', 'karim@test.com', $cooking, 'Cook', $bangladesh, $khulnaDivision, $khulna, 'Experienced cook in Khulna offering home cooking and catering.');
    $mizan = $makeArtisan('Mizan Rahman', 'mizan@test.com', $electrical, 'Electrician', $bangladesh, $khulnaDivision, $khulna, 'Trusted electrician in Khulna for home wiring and appliance repair.');

    return compact('luca', 'sophie', 'anna', 'karim', 'mizan');
}

test('a bare city name lists artisans working there', function () {
    $seeded = seedConciergeDirectory();

    [$ids, $message] = (new LocalConcierge)->answer('Basel');

    expect($ids)->toContain($seeded['luca']->id)
        ->and($ids)->toContain($seeded['anna']->id)
        ->and($ids)->not->toContain($seeded['sophie']->id)
        ->and($message)->toContain('Basel');
});

test('a bare country name lists artisans in that country', function () {
    $seeded = seedConciergeDirectory();

    [$ids] = (new LocalConcierge)->answer('France');

    expect($ids)->toContain($seeded['sophie']->id)
        ->and($ids)->not->toContain($seeded['luca']->id);
});

test('a service without a place asks for the country or city', function () {
    seedConciergeDirectory();

    [$ids, $message] = (new LocalConcierge)->answer('I need a plumber');

    expect($ids)->toBeEmpty()
        ->and($message)->toContain('country or city');
});

test('a science tutor request without a place asks for the country or city', function () {
    seedConciergeDirectory();

    [$ids, $message] = (new LocalConcierge)->answer('Can you help me to find a Science tutor in my city?');

    expect($ids)->toBeEmpty()
        ->and($message)->toContain('country or city');
});

test('service plus place returns the matching artisan', function () {
    $seeded = seedConciergeDirectory();

    [$ids] = (new LocalConcierge)->answer('I need a fitness instructor in Basel');

    expect($ids)->toContain($seeded['luca']->id);
});

test('a leaking tap request in Rennes finds the local plumber', function () {
    $seeded = seedConciergeDirectory();

    [$ids] = (new LocalConcierge)->answer('Find someone in Rennes to fix a leaking tap');

    expect($ids)->toContain($seeded['sophie']->id);
});

test('a follow-up place reuses the previously mentioned service', function () {
    $seeded = seedConciergeDirectory();

    $concierge = new LocalConcierge([
        ['role' => 'user', 'text' => 'I need a plumber'],
        ['role' => 'assistant', 'text' => 'Which country or city?'],
    ]);

    [$ids] = $concierge->answer('in Rennes');

    expect($ids)->toContain($seeded['sophie']->id);
});

test('a bare Khulna name lists artisans working there', function () {
    $seeded = seedConciergeDirectory();

    [$ids, $message] = (new LocalConcierge)->answer('Khulna');

    expect($ids)->toContain($seeded['karim']->id)
        ->and($ids)->toContain($seeded['mizan']->id)
        ->and($ids)->not->toContain($seeded['luca']->id)
        ->and($message)->toContain('Khulna');
});

test('a cook request in Khulna finds the local cook', function () {
    $seeded = seedConciergeDirectory();

    [$ids] = (new LocalConcierge)->answer('I need a cook in Khulna');

    expect($ids)->toContain($seeded['karim']->id);
});

test('off-topic messages stay in scope without results', function () {
    seedConciergeDirectory();

    [$ids, $message] = (new LocalConcierge)->answer('Write me a poem about the moon');

    expect($ids)->toBeEmpty()
        ->and($message)->toContain('Yaara Brains');
});

test('contact page renders the submit button inside a scrollable page', function () {
    $this->get(route('contact'))->assertOk()
        ->assertSee('Send Message', false)
        ->assertSee('app-page', false);
});

test('landing page renders country-only filter and Yaara Brains chat', function () {
    seedConciergeDirectory();

    $this->get('/')->assertOk()
        ->assertSee('Select your country', false)
        ->assertSee('Yaara Brains', false)
        ->assertSee('Describe what you need..', false)
        ->assertSee('I need a fitness instructor in Basel', false)
        ->assertDontSee('Select your state', false)
        ->assertDontSee('Select your city', false);
});
