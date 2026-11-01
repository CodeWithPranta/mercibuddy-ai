<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArtisanQueryController extends Controller
{
    public function store(Request $request, Artisan $artisan): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'query' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $artisan->queries()->create([
            'user_id' => $request->user()?->id,
            'phone' => $validated['phone'],
            'query' => $validated['query'],
        ]);

        return back()->with('message', 'Your query was sent to '.$artisan->full_name.'. They will be notified and get back to you.');
    }
}
