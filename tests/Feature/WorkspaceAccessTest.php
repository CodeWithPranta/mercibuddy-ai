<?php

use App\Models\User;
use Livewire\Volt\Volt;

function workspaceUser(int $userType): User
{
    return User::create([
        'name' => "Workspace User {$userType}",
        'email' => "workspace-{$userType}-".uniqid().'@test.com',
        'password' => 'password',
        'user_type' => $userType,
        'email_verified_at' => now(),
    ]);
}

test('guests are sent to login with the artisan profile kept as the intended url', function () {
    $seeded = seedRouteDirectory();
    $url = '/artisan/'.$seeded['liked']->id.'/liked-artisan';

    $this->get($url)->assertRedirect('/login');

    expect(session('url.intended'))->toBe(url($url));
});

test('login returns to the intended artisan profile', function () {
    $seeded = seedRouteDirectory();
    $user = workspaceUser(0);
    $url = '/artisan/'.$seeded['liked']->id.'/liked-artisan';
    session()->put('url.intended', $url);

    Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertRedirect($url);
});

test('register returns to the intended artisan profile', function () {
    $seeded = seedRouteDirectory();
    $url = '/artisan/'.$seeded['liked']->id.'/liked-artisan';
    session()->put('url.intended', $url);

    Volt::test('pages.auth.register')
        ->set('name', 'Intent User')
        ->set('email', 'intent-user@test.com')
        ->set('user_type', '0')
        ->set('agree', true)
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertRedirect($url);
});

test('profile links artisans and both-type users to their dashboard', function () {
    foreach ([2, 3] as $userType) {
        $this->actingAs(workspaceUser($userType))
            ->get('/profile')
            ->assertOk()
            ->assertSee('Open Artisan Dashboard')
            ->assertSee('/artisan', false);
    }
});

test('profile links admins to the admin panel only', function () {
    $this->actingAs(workspaceUser(1))
        ->get('/profile')
        ->assertOk()
        ->assertSee('Open Admin Panel')
        ->assertSee('/admin', false)
        ->assertDontSee('Open Artisan Dashboard');
});

test('profile shows no workspace links to general users', function () {
    $this->actingAs(workspaceUser(0))
        ->get('/profile')
        ->assertOk()
        ->assertDontSee('Open Artisan Dashboard')
        ->assertDontSee('Open Admin Panel');
});

test('both panels expose a login page', function () {
    $this->get('/admin/login')->assertOk();
    $this->get('/artisan/login')->assertOk();
});

test('artisans can open their panel but not the admin panel', function () {
    $this->actingAs(workspaceUser(2))->get('/artisan')->assertOk();
    $this->actingAs(workspaceUser(2))->get('/admin')->assertForbidden();
});

test('admins can open the admin panel', function () {
    $this->actingAs(workspaceUser(1))->get('/admin')->assertOk();
});

test('general users cannot open the artisan panel', function () {
    $this->actingAs(workspaceUser(0))->get('/artisan')->assertForbidden();
});
