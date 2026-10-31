<?php

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

function adminUser(): User
{
    return User::create([
        'name' => 'Category Admin',
        'email' => 'category-admin-'.uniqid().'@test.com',
        'password' => 'password',
        'user_type' => 1,
        'email_verified_at' => now(),
    ]);
}

test('admin can create a category with profession options', function () {
    $this->actingAs(adminUser());
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Photography',
            'slug' => 'photography',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg"></svg>',
            'professions' => ['Photographer', 'Photo Retoucher'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $category = Category::where('slug', 'photography')->firstOrFail();

    expect($category->professions)->toBe(['Photographer', 'Photo Retoucher']);
});

test('category creation requires at least one profession option', function () {
    $this->actingAs(adminUser());
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Photography',
            'slug' => 'photography',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg"></svg>',
            'professions' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['professions']);

    expect(Category::where('slug', 'photography')->exists())->toBeFalse();
});

test('admin can update the profession options of a category', function () {
    $this->actingAs(adminUser());
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $category = Category::create([
        'name' => 'Photography',
        'slug' => 'photography',
        'icon' => 'x',
        'professions' => ['Photographer'],
    ]);

    Livewire::test(EditCategory::class, ['record' => $category->id])
        ->fillForm(['professions' => ['Photographer', 'Drone Pilot']])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->fresh()->professions)->toBe(['Photographer', 'Drone Pilot']);
});

test('profession options are read from the stored category professions', function () {
    $category = Category::create([
        'name' => 'Photography',
        'slug' => 'photography',
        'icon' => 'x',
        'professions' => ['Photographer', 'Photo Retoucher'],
    ]);

    $options = Category::professionOptions($category->id);

    expect($options)
        ->toHaveKey('Photographer')
        ->toHaveKey('Photo Retoucher')
        ->not->toHaveKey('Cleaner')
        ->and(Category::professionOptions(null))->toBe([]);
});

test('profession options merge stored professions with used professions', function () {
    $category = Category::create([
        'name' => 'Photography',
        'slug' => 'photography',
        'icon' => 'x',
        'professions' => ['Photographer'],
    ]);

    $user = User::create([
        'name' => 'Studio Owner',
        'email' => 'studio-owner@test.com',
        'password' => 'password',
        'user_type' => 2,
    ]);

    $country = Country::create(['shortname' => 'BD', 'name' => 'Bangladesh', 'slug' => 'bangladesh', 'phonecode' => '+880']);
    $state = State::create(['name' => 'Dhaka', 'country_id' => $country->id]);
    $city = City::create(['name' => 'Dhaka', 'state_id' => $state->id]);

    $category->artisans()->create([
        'full_name' => 'Studio Owner',
        'profile_photo' => 'photo.jpg',
        'cover_photo' => 'cover.jpg',
        'user_id' => $user->id,
        'experience_in_year' => '5',
        'last_education' => 'Diploma',
        'date_of_birth' => '1990-01-01',
        'profession' => 'Videographer',
        'country_id' => $country->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => '1 Test Street',
        'biography' => 'Bio',
    ]);

    expect(Category::professionOptions($category->id))
        ->toHaveKey('Photographer')
        ->toHaveKey('Videographer');
});
