<?php

namespace App\Http\Middleware;

use App\Models\FilterValue;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictFilteredArtisans
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Check if 'country_id' and 'category_id' are set in the user's filter values
            $user = Auth::user();
            $filterValues = FilterValue::where('user_id', $user->id)->first();

            if (! $filterValues || ! $filterValues->country_id || ! $filterValues->category_id) {
                // If 'country_id' or 'category_id' is missing, redirect or show an error
                return redirect('/');
                // You can replace '/' with the URL where you want to redirect the user
            }
        } elseif (! $request->session()->has('filter_values.country_id') || ! $request->session()->has('filter_values.category_id')) {
            // If 'country_id' or 'category_id' is missing in the session, redirect or show an error
            return redirect('/');
            // You can replace '/' with the URL where you want to redirect the user
        }

        return $next($request);
    }
}
