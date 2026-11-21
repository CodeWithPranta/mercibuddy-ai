<?php

use App\Filament\Artisan\Pages\ArtisanProfile;
use App\Models\Artisan;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Service;
use App\Models\State;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;
use Livewire\Livewire;

function makeAttachmentArtisan(string $suffix, ?User $owner = null): Artisan
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
        'full_name' => 'Attachment Artisan '.$suffix,
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

function makeAttachmentCustomer(string $suffix): User
{
    return User::create([
        'name' => 'Viewer '.$suffix,
        'email' => 'viewer-'.$suffix.'@test.com',
        'password' => 'password',
        'user_type' => 0,
        'email_verified_at' => now(),
    ]);
}

test('the artisan dashboard profile form accepts a video cv link', function () {
    $owner = makeAttachmentCustomer('profile-form');
    $owner->forceFill(['user_type' => 2])->save();
    makeAttachmentArtisan('profile-form', $owner);

    $this->actingAs($owner);
    Filament::setCurrentPanel(Filament::getPanel('artisan'));

    $form = Livewire::test(ArtisanProfile::class)->instance()->getSchema('form');

    expect($form->getComponentByStatePath('video_cv'))
        ->toBeInstanceOf(TextInput::class);
});

test('the paperclip icon only appears when a video cv is attached', function () {
    $artisan = makeAttachmentArtisan('attachment');

    $this->actingAs(makeAttachmentCustomer('attachment-viewer'))
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertSee('Personal Statement')
        ->assertDontSee('View attached file');

    $artisan->update(['video_cv' => 'https://example.com/video-cv.mp4']);

    $this->actingAs(makeAttachmentCustomer('attachment-viewer-2'))
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertSee('View attached file')
        ->assertSee('https://example.com/video-cv.mp4');

    $artisan->update(['video_cv' => 'attachments/missing-'.Str::random(8).'.mp4']);

    $this->actingAs(makeAttachmentCustomer('attachment-viewer-3'))
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertDontSee('View attached file');
});

test('the service attachment icon links to an available service image', function () {
    $artisan = makeAttachmentArtisan('service-link');

    Service::create([
        'title' => 'Deep cleaning',
        'user_id' => $artisan->user_id,
        'slug' => 'deep-cleaning-service-link',
        'featured_image' => 'https://example.com/service.jpg',
        'content' => 'A thorough deep cleaning service for homes and offices.',
    ]);

    $this->actingAs(makeAttachmentCustomer('service-link-viewer'))
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertSee('View service attachment')
        ->assertSee('https://example.com/service.jpg');
});

test('the service attachment icon is hidden when no service file is available', function () {
    $artisan = makeAttachmentArtisan('service-missing');

    Service::create([
        'title' => 'Deep cleaning',
        'user_id' => $artisan->user_id,
        'slug' => 'deep-cleaning-service-missing',
        'featured_image' => 'services/missing-'.Str::random(12).'.jpg',
        'content' => 'A thorough deep cleaning service for homes and offices.',
    ]);

    $this->actingAs(makeAttachmentCustomer('service-missing-viewer'))
        ->get(route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ]))
        ->assertOk()
        ->assertDontSee('View service attachment');
});
