<?php

use App\Models\Artisan;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

function seedShareDirectory(): array
{
    $cleaning = Category::create(['name' => 'Cleaning', 'slug' => 'cleaning', 'icon' => 'x']);
    $bangladesh = Country::create(['shortname' => 'BD', 'name' => 'Bangladesh', 'slug' => 'bangladesh', 'phonecode' => '+880']);
    $dhakaState = State::create(['name' => 'Dhaka', 'country_id' => $bangladesh->id]);
    $dhaka = City::create(['name' => 'Dhaka', 'state_id' => $dhakaState->id]);

    $user = User::create(['name' => 'Sharable Artisan', 'email' => 'sharable@test.com', 'password' => 'password', 'user_type' => 2]);

    $artisan = Artisan::create([
        'full_name' => 'Sharable Artisan',
        'profile_photo' => 'photo.jpg',
        'cover_photo' => 'cover.jpg',
        'user_id' => $user->id,
        'category_id' => $cleaning->id,
        'experience_in_year' => '5',
        'last_education' => 'Diploma',
        'date_of_birth' => '1990-01-01',
        'profession' => 'Cleaner',
        'country_id' => $bangladesh->id,
        'state_id' => $dhakaState->id,
        'city_id' => $dhaka->id,
        'address' => '1 Test Street',
        'biography' => 'Bio',
    ]);

    return compact('cleaning', 'bangladesh', 'artisan');
}

function shareProfileUrl(Artisan $artisan): string
{
    return route('artisan.show', ['artisan' => $artisan->id, 'slug' => Str::slug($artisan->full_name)]);
}

function shareExpectations(string $html, Artisan $artisan, string $url): void
{
    $encodedUrl = rawurlencode($url);
    $encodedName = rawurlencode($artisan->full_name);

    expect($html)
        ->toContain("shareMenu('{$url}'")
        ->toContain('copyLink()')
        ->toContain('shareNative()')
        ->toContain('https://www.facebook.com/sharer/sharer.php?u='.$encodedUrl)
        ->toContain('https://wa.me/?text='.rawurlencode($artisan->full_name.' '.$url))
        ->toContain('https://twitter.com/intent/tweet?text='.$encodedName.'&amp;url='.$encodedUrl)
        ->toContain('https://t.me/share/url?url='.$encodedUrl.'&amp;text='.$encodedName)
        ->toContain('https://www.linkedin.com/sharing/share-offsite/?url='.$encodedUrl)
        ->toContain('mailto:?subject='.$encodedName.'&amp;body='.$encodedUrl)
        ->toContain('sms:?&amp;body='.rawurlencode($artisan->full_name.' '.$url));
}

test('listing share menu shares the exact artisan profile link', function () {
    URL::forceRootUrl('http://localhost');

    $seeded = seedShareDirectory();
    $artisan = $seeded['artisan'];
    $url = shareProfileUrl($artisan);

    $html = $this->get('/artisans/bangladesh/cleaning')->getContent();

    shareExpectations($html, $artisan, $url);
});

test('profile page share menu shares the exact artisan profile link', function () {
    URL::forceRootUrl('http://localhost');

    $seeded = seedShareDirectory();
    $artisan = $seeded['artisan'];
    $url = shareProfileUrl($artisan);

    $viewer = User::create([
        'name' => 'Share Viewer',
        'email' => 'share-viewer@test.com',
        'password' => 'password',
        'user_type' => 0,
        'email_verified_at' => now(),
    ]);

    $html = $this->actingAs($viewer)
        ->get('/artisan/'.$artisan->id.'/'.Str::slug($artisan->full_name))
        ->getContent();

    shareExpectations($html, $artisan, $url);
});
