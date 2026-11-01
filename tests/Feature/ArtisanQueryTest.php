<?php

use App\Livewire\FooterSection;
use App\Models\Artisan;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{0: User, 1: Artisan}
 */
function makeArtisanForQueries(string $suffix): array
{
    $category = Category::create(['name' => 'Cleaning', 'slug' => 'cleaning-'.$suffix, 'icon' => 'x']);
    $country = Country::create(['shortname' => 'CH', 'name' => 'Switzerland', 'slug' => 'switzerland-'.$suffix, 'phonecode' => '+41']);
    $state = State::create(['name' => 'Basel', 'country_id' => $country->id]);
    $city = City::create(['name' => 'Basel', 'state_id' => $state->id]);

    $artisanUser = User::create([
        'name' => 'Query Artisan '.$suffix,
        'email' => 'query-artisan-'.$suffix.'@test.com',
        'password' => 'password',
        'user_type' => 2,
        'email_verified_at' => now(),
    ]);

    $artisan = Artisan::create([
        'full_name' => 'Query Artisan '.$suffix,
        'profile_photo' => 'photo.jpg',
        'cover_photo' => 'cover.jpg',
        'user_id' => $artisanUser->id,
        'category_id' => $category->id,
        'experience_in_year' => '5',
        'date_of_birth' => '1990-01-01',
        'profession' => 'Cleaner',
        'currency' => 'CHF',
        'country_id' => $country->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => '1 Test Street',
        'biography' => 'Bio',
    ]);

    return [$artisanUser, $artisan];
}

function makeQueryCustomer(string $suffix): User
{
    return User::create([
        'name' => 'Customer '.$suffix,
        'email' => 'customer-'.$suffix.'@test.com',
        'password' => 'password',
        'user_type' => 0,
        'email_verified_at' => now(),
    ]);
}

test('guests cannot send a query', function () {
    [, $artisan] = makeArtisanForQueries('guest');

    $this->post(route('artisan.query.store', $artisan), [
        'phone' => '+41 79 000 00 00',
        'query' => 'Please fix my leaking tap this week.',
    ])->assertRedirect('/login');

    $this->assertDatabaseCount('artisan_queries', 0);
});

test('a customer can send a phone query to an artisan', function () {
    [, $artisan] = makeArtisanForQueries('send');
    $customer = makeQueryCustomer('send');

    $this->actingAs($customer)
        ->post(route('artisan.query.store', $artisan), [
            'phone' => '+41 79 000 00 00',
            'query' => 'Please fix my leaking tap this week.',
        ])
        ->assertRedirect()
        ->assertSessionHas('message');

    $this->assertDatabaseHas('artisan_queries', [
        'artisan_id' => $artisan->id,
        'user_id' => $customer->id,
        'phone' => '+41 79 000 00 00',
        'query' => 'Please fix my leaking tap this week.',
        'read_at' => null,
    ]);
});

test('a query requires a phone number and details', function () {
    [, $artisan] = makeArtisanForQueries('validation');
    $customer = makeQueryCustomer('validation');

    $this->actingAs($customer)
        ->post(route('artisan.query.store', $artisan), [])
        ->assertSessionHasErrors(['phone', 'query']);
});

test('artisans see customer queries and they are marked as read', function () {
    [$artisanUser, $artisan] = makeArtisanForQueries('inbox');
    $customer = makeQueryCustomer('inbox');

    $artisan->queries()->create([
        'user_id' => $customer->id,
        'phone' => '+41 78 111 22 33',
        'query' => 'Can you clean a two-room flat on Friday?',
    ]);

    $this->actingAs($artisanUser)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Customer queries')
        ->assertSee('+41 78 111 22 33')
        ->assertSee('Can you clean a two-room flat on Friday?');

    expect($artisan->queries()->unread()->count())->toBe(0);
});

test('customers see the queries they sent', function () {
    [, $artisan] = makeArtisanForQueries('sent');
    $customer = makeQueryCustomer('sent');

    $artisan->queries()->create([
        'user_id' => $customer->id,
        'phone' => '+41 78 111 22 33',
        'query' => 'Can you clean a two-room flat on Friday?',
    ]);

    $this->actingAs($customer)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Your queries')
        ->assertSee('Query Artisan sent')
        ->assertSee('Can you clean a two-room flat on Friday?');
});

test('the footer shows alerts for artisans and customers with queries', function () {
    [$artisanUser, $artisan] = makeArtisanForQueries('footer');
    $customer = makeQueryCustomer('footer');

    $artisan->queries()->create([
        'user_id' => $customer->id,
        'phone' => '+41 78 111 22 33',
        'query' => 'Can you clean a two-room flat on Friday?',
    ]);

    $this->actingAs($artisanUser);
    Livewire::test(FooterSection::class)
        ->assertSee('Alerts')
        ->assertDontSee('About');

    $this->actingAs($customer);
    Livewire::test(FooterSection::class)
        ->assertSee('Alerts')
        ->assertDontSee('About');
});

test('the footer keeps the about link for users without queries', function () {
    $customer = makeQueryCustomer('about');

    $this->actingAs($customer);
    Livewire::test(FooterSection::class)
        ->assertSee('About')
        ->assertDontSee('Alerts');
});

test('the artisan profile bell opens the query form', function () {
    [, $artisan] = makeArtisanForQueries('bell');
    $customer = makeQueryCustomer('bell');

    $this->actingAs($customer)
        ->get(route('artisan.show', ['artisan' => $artisan->id, 'slug' => 'query-artisan-bell']))
        ->assertOk()
        ->assertSee('Send a query')
        ->assertSee(route('artisan.query.store', $artisan));
});
