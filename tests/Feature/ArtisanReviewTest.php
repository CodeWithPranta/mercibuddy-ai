<?php

use App\Filament\Artisan\Pages\ArtisanProfile;
use App\Livewire\ArtisanReviews;
use App\Models\Artisan;
use App\Models\Category;
use App\Models\City;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Service;
use App\Models\State;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;
use Livewire\Livewire;

function makeReviewArtisan(string $suffix, ?User $owner = null): Artisan
{
    $category = Category::create(['name' => 'Cleaning', 'slug' => 'cleaning-'.$suffix, 'icon' => 'x']);
    $country = Country::create(['shortname' => 'CH', 'name' => 'Switzerland', 'slug' => 'switzerland-'.$suffix, 'phonecode' => '+41']);
    $state = State::create(['name' => 'Basel', 'country_id' => $country->id]);
    $city = City::create(['name' => 'Basel', 'state_id' => $state->id]);

    $owner ??= User::create([
        'name' => 'Artisan Owner '.$suffix,
        'email' => 'artisan-owner-'.$suffix.'@test.com',
        'password' => 'password',
        'user_type' => 2,
        'email_verified_at' => now(),
    ]);

    return Artisan::create([
        'full_name' => 'Review Artisan '.$suffix,
        'profile_photo' => 'photo.jpg',
        'cover_photo' => 'cover.jpg',
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'experience_in_year' => 5,
        'date_of_birth' => '1990-01-01',
        'profession' => 'Cleaner',
        'currency' => 'CHF',
        'country_id' => $country->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => '1 Test Street',
        'biography' => 'Bio',
    ]);
}

function makeReviewCustomer(string $suffix): User
{
    return User::create([
        'name' => 'Reviewer '.$suffix,
        'email' => 'reviewer-'.$suffix.'@test.com',
        'password' => 'password',
        'user_type' => 0,
        'email_verified_at' => now(),
    ]);
}

test('a customer can leave a review with a rating and comment', function () {
    $artisan = makeReviewArtisan('create');
    $customer = makeReviewCustomer('create');

    $this->actingAs($customer);

    Livewire::test(ArtisanReviews::class, ['artisan' => $artisan])
        ->set('rating', 4)
        ->set('comment', 'Very professional and finished on time.')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('comments', [
        'user_id' => $customer->id,
        'artisan_id' => $artisan->id,
        'rating' => 4,
        'comment' => 'Very professional and finished on time.',
    ]);
});

test('a review requires a rating and a meaningful comment', function () {
    $artisan = makeReviewArtisan('validate');
    $customer = makeReviewCustomer('validate');

    $this->actingAs($customer);

    Livewire::test(ArtisanReviews::class, ['artisan' => $artisan])
        ->set('rating', null)
        ->set('comment', '')
        ->call('submit')
        ->assertHasErrors(['rating' => 'required', 'comment' => 'required']);
});

test('a review rating must be between one and five', function () {
    $artisan = makeReviewArtisan('range');
    $customer = makeReviewCustomer('range');

    $this->actingAs($customer);

    Livewire::test(ArtisanReviews::class, ['artisan' => $artisan])
        ->set('rating', 6)
        ->set('comment', 'This rating is out of the allowed range.')
        ->call('submit')
        ->assertHasErrors(['rating' => 'between']);
});

test('submitting a review again updates the existing review', function () {
    $artisan = makeReviewArtisan('update');
    $customer = makeReviewCustomer('update');

    Comment::create([
        'user_id' => $customer->id,
        'artisan_id' => $artisan->id,
        'rating' => 3,
        'comment' => 'The first version of my review.',
    ]);

    $this->actingAs($customer);

    Livewire::test(ArtisanReviews::class, ['artisan' => $artisan])
        ->set('rating', 5)
        ->set('comment', 'Updated: excellent work after all.')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseCount('comments', 1);
    $this->assertDatabaseHas('comments', [
        'user_id' => $customer->id,
        'artisan_id' => $artisan->id,
        'rating' => 5,
        'comment' => 'Updated: excellent work after all.',
    ]);
});

test('an artisan cannot review their own profile', function () {
    $owner = makeReviewCustomer('owner');
    $owner->forceFill(['user_type' => 2])->save();
    $artisan = makeReviewArtisan('self', $owner);

    $this->actingAs($owner);

    Livewire::test(ArtisanReviews::class, ['artisan' => $artisan])
        ->set('rating', 5)
        ->set('comment', 'I am reviewing my own profile.')
        ->call('submit');

    $this->assertDatabaseCount('comments', 0);
});

test('the profile page shows the average rating and reviews', function () {
    $artisan = makeReviewArtisan('display');
    $customer = makeReviewCustomer('display');

    Comment::create([
        'user_id' => $customer->id,
        'artisan_id' => $artisan->id,
        'rating' => 4,
        'comment' => 'Friendly, tidy and reliable.',
    ]);

    $this->actingAs($customer)
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertSee('Customer rating')
        ->assertSee('4.0')
        ->assertSee('Good')
        ->assertSee('Friendly, tidy and reliable.')
        ->assertSee($customer->name);
});

test('average rating and breakdown ignore unrated comments', function () {
    $artisan = makeReviewArtisan('stats');
    $first = makeReviewCustomer('stats-1');
    $second = makeReviewCustomer('stats-2');

    Comment::create(['user_id' => $first->id, 'artisan_id' => $artisan->id, 'rating' => 5, 'comment' => 'Excellent service.']);
    Comment::create(['user_id' => $second->id, 'artisan_id' => $artisan->id, 'rating' => 3, 'comment' => 'Average service.']);
    Comment::create(['user_id' => makeReviewCustomer('stats-3')->id, 'artisan_id' => $artisan->id, 'rating' => null, 'comment' => 'Legacy comment.']);

    $artisan->refresh();

    expect($artisan->reviewCount())->toBe(2)
        ->and($artisan->averageRating())->toBe(4.0)
        ->and($artisan->ratingBreakdown())->toBe([5 => 1, 4 => 0, 3 => 1, 2 => 0, 1 => 0])
        ->and($artisan->ratingLabel())->toBe('Good');
});

test('the artisan dashboard profile form accepts a video cv link', function () {
    $owner = makeReviewCustomer('profile-form');
    $owner->forceFill(['user_type' => 2])->save();
    makeReviewArtisan('profile-form', $owner);

    $this->actingAs($owner);
    Filament::setCurrentPanel(Filament::getPanel('artisan'));

    $form = Livewire::test(ArtisanProfile::class)->instance()->getSchema('form');

    expect($form->getComponentByStatePath('video_cv'))
        ->toBeInstanceOf(TextInput::class);
});

test('the paperclip icon only appears when a video cv is attached', function () {
    $artisan = makeReviewArtisan('attachment');

    $this->actingAs(makeReviewCustomer('attachment-viewer'))
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertSee('Personal Statement')
        ->assertDontSee('View attached file');

    $artisan->update(['video_cv' => 'https://example.com/video-cv.mp4']);

    $this->actingAs(makeReviewCustomer('attachment-viewer-2'))
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertSee('View attached file')
        ->assertSee('https://example.com/video-cv.mp4');

    $artisan->update(['video_cv' => 'attachments/missing-'.Str::random(8).'.mp4']);

    $this->actingAs(makeReviewCustomer('attachment-viewer-3'))
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertDontSee('View attached file');
});

test('the service attachment icon links to an available service image', function () {
    $artisan = makeReviewArtisan('service-link');

    Service::create([
        'title' => 'Deep cleaning',
        'user_id' => $artisan->user_id,
        'slug' => 'deep-cleaning-service-link',
        'featured_image' => 'https://example.com/service.jpg',
        'content' => 'A thorough deep cleaning service for homes and offices.',
    ]);

    $this->actingAs(makeReviewCustomer('service-link-viewer'))
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertSee('View service attachment')
        ->assertSee('https://example.com/service.jpg');
});

test('the service attachment icon is hidden when no service file is available', function () {
    $artisan = makeReviewArtisan('service-missing');

    Service::create([
        'title' => 'Deep cleaning',
        'user_id' => $artisan->user_id,
        'slug' => 'deep-cleaning-service-missing',
        'featured_image' => 'services/missing-'.Str::random(12).'.jpg',
        'content' => 'A thorough deep cleaning service for homes and offices.',
    ]);

    $this->actingAs(makeReviewCustomer('service-missing-viewer'))
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertDontSee('View service attachment');
});
