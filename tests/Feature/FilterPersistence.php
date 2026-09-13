<?php

use App\Livewire\FilterSection;
use App\Models\Category;
use App\Models\Country;
use App\Models\FilterValue;
use App\Models\User;
use Livewire\Livewire;

function seedFilterPersistence(): array
{
    $cleaning = Category::create(['name' => 'Cleaning', 'slug' => 'cleaning', 'icon' => 'x']);
    $tutoring = Category::create(['name' => 'Tutoring', 'slug' => 'tutoring', 'icon' => 'x']);

    $bangladesh = Country::create(['shortname' => 'BD', 'name' => 'Bangladesh', 'slug' => 'bangladesh', 'phonecode' => '+880']);

    return compact('cleaning', 'tutoring', 'bangladesh');
}

test('guest returning to the landing page sees the previous category highlighted', function () {
    $seeded = seedFilterPersistence();

    $this->withSession(['filter_values' => [
        'country_id' => $seeded['bangladesh']->id,
        'state_id' => null,
        'city_id' => null,
        'category_id' => $seeded['cleaning']->id,
    ]]);

    Livewire::test(FilterSection::class)
        ->assertSet('selectedCountry', $seeded['bangladesh']->id)
        ->assertSet('selectedCategory', $seeded['cleaning']->id);
});

test('authenticated user returning sees the previous category highlighted', function () {
    $seeded = seedFilterPersistence();

    $user = User::create([
        'name' => 'Filter User',
        'email' => 'filter@test.com',
        'password' => 'password',
        'user_type' => 0,
        'email_verified_at' => now(),
    ]);

    FilterValue::create([
        'user_id' => $user->id,
        'country_id' => $seeded['bangladesh']->id,
        'state_id' => null,
        'city_id' => null,
        'category_id' => $seeded['cleaning']->id,
    ]);

    $this->actingAs($user);

    Livewire::test(FilterSection::class)
        ->assertSet('selectedCountry', $seeded['bangladesh']->id)
        ->assertSet('selectedCategory', $seeded['cleaning']->id);
});

test('clicking a new category replaces the previous highlight', function () {
    $seeded = seedFilterPersistence();

    $this->withSession(['filter_values' => [
        'country_id' => $seeded['bangladesh']->id,
        'state_id' => null,
        'city_id' => null,
        'category_id' => $seeded['cleaning']->id,
    ]]);

    Livewire::test(FilterSection::class)
        ->assertSet('selectedCategory', $seeded['cleaning']->id)
        ->call('updateSelectedCategory', $seeded['tutoring']->id)
        ->assertSet('selectedCategory', $seeded['tutoring']->id)
        ->assertRedirect('/artisans/bangladesh/tutoring');
});

test('changing the country clears the category highlight', function () {
    $seeded = seedFilterPersistence();

    $this->withSession(['filter_values' => [
        'country_id' => $seeded['bangladesh']->id,
        'state_id' => null,
        'city_id' => null,
        'category_id' => $seeded['cleaning']->id,
    ]]);

    Livewire::test(FilterSection::class)
        ->assertSet('selectedCategory', $seeded['cleaning']->id)
        ->set('selectedCountry', null)
        ->assertSet('selectedCategory', null);
});
