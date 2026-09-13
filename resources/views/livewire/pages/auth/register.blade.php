<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $user_type = '';
    public string $agree = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'user_type' => ['required', 'in:0,2,3'],
            'agree' => ['required'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(session('url.intended', RouteServiceProvider::HOME), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <fieldset class="border p-2 rounded-md border-gray-300">
                <legend class="text-gray-700 text-sm">Account Type</legend>
                <div class="flex flex-wrap justify-between">
                <div>
                    <input wire:model="user_type" value="0" type="radio" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="user_type">
                    <span class="text-sm text-gray-600">{{__('General User')}}</span>
                </div>
                <div>
                    <input wire:model="user_type" value="2" type="radio" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="user_type">
                    <span class="text-sm text-gray-600">{{__('Skilled Artisan')}}</span>
                </div>
                <div>
                    <input wire:model="user_type" value="3" type="radio" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="user_type">
                    <span class="text-sm text-gray-600">{{__('Both')}}</span>
                    <x-input-error :messages="$errors->get('user_type')" class="mt-2" />
                </div>
                </div>
            </fieldset>
        </div>
        <div class="mt-4">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="agree" class="inline-flex items-center">
                <input wire:model="agree" id="agree" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="agree">
                <span class="ms-2 text-sm text-gray-600">{{ __('I have read and agree to the') }} <a href="{{url('page/terms-of-use')}}" class="text-red-500">{{__('Terms of Use')}}</a> and <a href="{{url('page/privacy-policy')}}" class="text-red-500">{{__('Privacy Policy')}}</a></span>
                <x-input-error :messages="$errors->get('agree')" class="mt-2" />
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm pl-2 text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                <span>{{__('Register')}}</span>
            </x-primary-button>
        </div>

        <span wire:loading class="text-green-500">{{__('Processing...')}}</span>

    </form>
</div>
