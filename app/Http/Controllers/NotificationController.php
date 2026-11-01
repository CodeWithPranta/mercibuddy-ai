<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $artisan = $user->isArtisan() ? $user->artisanProfile : null;

        if ($artisan) {
            $queries = $artisan->queries()->with('user')->latest()->get();

            $artisan->queries()->unread()->update(['read_at' => now()]);

            return view('notifications', [
                'setting' => Setting::first(),
                'artisan' => $artisan,
                'queries' => $queries,
            ]);
        }

        return view('notifications', [
            'setting' => Setting::first(),
            'artisan' => null,
            'queries' => $user->artisanQueries()->with('artisan')->latest()->get(),
        ]);
    }
}
