<?php

use App\Models\User;

function verifiedProfileUser(): User
{
    return User::create([
        'name' => 'Profile User',
        'email' => 'profile-user@test.com',
        'password' => 'password',
        'user_type' => 0,
        'email_verified_at' => now(),
    ]);
}

test('guests cannot open the profile page', function () {
    $this->get('/profile')->assertRedirect('/login');
});

test('profile page shows a logout option for logged in users', function () {
    $this->actingAs(verifiedProfileUser())
        ->get('/profile')
        ->assertOk()
        ->assertSee('Log out', false)
        ->assertSee('Logged in', false)
        ->assertSee('/logout', false);
});

test('footer shows the logged in state on the account tab', function () {
    $this->actingAs(verifiedProfileUser())
        ->get('/')
        ->assertOk()
        ->assertSee('app-nav-dot', false)
        ->assertSee('Profile', false);
});

test('footer shows a plain account tab for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Account', false)
        ->assertDontSee('app-nav-dot', false);
});

test('users can log out', function () {
    $response = $this->actingAs(verifiedProfileUser())
        ->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('guests cannot log out', function () {
    $this->post('/logout')->assertRedirect('/login');
});
