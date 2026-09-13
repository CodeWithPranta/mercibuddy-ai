<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Laravel\Socialite\Facades\Socialite as FacadesSocialite;

class GoogleSocialiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return FacadesSocialite::driver('google')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleCallback()
    {
        try {

            $user = FacadesSocialite::driver('google')->user();

            $finduser = User::where('social_id', $user->id)->first();

            if ($finduser) {

                $this->ensureBothTypeAccess($finduser);

                FacadesAuth::login($finduser);

                return redirect()->intended('/');

            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'social_id' => $user->id,
                    'social_type' => 'google',
                    'user_type' => 3,
                    'email_verified_at' => now(),
                    'password' => encrypt('my-google'),
                ]);

                FacadesAuth::login($newUser);

                return redirect()->intended('/');
            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * Everyone who signs in with Gmail counts as a "Both" user: existing
     * Google accounts stuck on General (0) are upgraded, and Google
     * emails are trusted as verified so the profile pages open directly.
     * Deliberate Admin (1) or Artisan-only (2) assignments are untouched.
     */
    private function ensureBothTypeAccess(User $user): void
    {
        $updates = [];

        if ($user->social_type === 'google' && (int) $user->user_type === 0) {
            $updates['user_type'] = 3;
        }

        if ($user->email_verified_at === null) {
            $updates['email_verified_at'] = now();
        }

        if ($updates !== []) {
            $user->update($updates);
        }
    }
}
