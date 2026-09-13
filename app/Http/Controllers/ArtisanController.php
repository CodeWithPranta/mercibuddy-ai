<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use App\Models\Category;
use App\Models\ContactDetail;
use App\Models\Country;
use App\Models\FilterValue;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ArtisanController extends Controller
{
    public function index(string $country, string $category)
    {
        $country = $this->resolveCountry($country);
        $category = Category::where('slug', $category)->firstOrFail();

        return view('artisans', [
            'setting' => Setting::first(),
            'country' => $country,
            'category' => $category,
        ]);
    }

    /**
     * Legacy session-based listing URL. Redirects to the shareable
     * country/category URL so old links keep working.
     */
    public function legacyIndex()
    {
        $filterValues = auth()->check()
            ? FilterValue::where('user_id', auth()->id())->first()
            : (object) session('filter_values', []);

        $country = $filterValues?->country_id ? Country::find($filterValues->country_id) : null;
        $category = $filterValues?->category_id ? Category::find($filterValues->category_id) : null;

        if (! $country || ! $category) {
            return redirect('/');
        }

        return redirect()->route('artisans.index', [
            'country' => $country->slug ?: Str::slug($country->name),
            'category' => $category->slug ?: Str::slug($category->name),
        ], 301);
    }

    public function detail(Artisan $artisan, ?string $slug = null)
    {
        $canonicalSlug = Str::slug($artisan->full_name);

        if ($slug !== $canonicalSlug) {
            return redirect()->route('artisan.show', [
                'artisan' => $artisan->id,
                'slug' => $canonicalSlug,
            ], 301);
        }

        $contactMedia = ContactDetail::where('user_id', $artisan->user_id)->first();

        return view('artisan-detail', [
            'setting' => Setting::first(),
            'artisan' => $artisan,
            'media' => $contactMedia,
            'services' => Service::where('user_id', $artisan->user_id)->get(),
        ]);
    }

    /**
     * Legacy profile URL. Redirects to the canonical slug URL.
     */
    public function legacyDetail($id)
    {
        $artisan = Artisan::findOrFail($id);

        return redirect()->route('artisan.show', [
            'artisan' => $artisan->id,
            'slug' => Str::slug($artisan->full_name),
        ], 301);
    }

    private function resolveCountry(string $value): Country
    {
        if (Schema::hasColumn('countries', 'slug')) {
            $country = Country::where('slug', $value)->first();

            if ($country) {
                return $country;
            }
        }

        // Fallback for DBs where the slug migration has not run yet,
        // or for legacy name-based URLs: match by slugified name.
        $country = Country::all()->first(fn (Country $c) => Str::slug($c->name) === $value);

        return $country ?? Country::where('name', $value)->firstOrFail();
    }
}
