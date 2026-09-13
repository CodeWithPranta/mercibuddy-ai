<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Country;
use App\Models\FilterValue;
use App\Models\Setting;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FilterSection extends Component
{
    public $countries;

    public $categories;

    #[Rule('required')]
    #[Validate('required', message: 'Please select a country.')]
    public $selectedCountry;

    #[Rule('required')]
    public $selectedCategory;

    public function mount()
    {
        // Remember the country (useful when coming back), but always start
        // with no category highlighted so a previous choice never looks
        // pre-selected when the visitor returns to this page.
        $this->selectedCategory = null;

        if (auth()->check()) {
            // User is authenticated, load filter values from the database
            $user = auth()->user();
            $filterValues = FilterValue::where('user_id', $user->id)->first();

            if ($filterValues) {
                $this->selectedCountry = $filterValues->country_id;
            }
        } elseif (session()->has('filter_values')) {
            // User is not authenticated, check for session filter values
            $sessionValues = session('filter_values');
            $this->selectedCountry = $sessionValues['country_id'];
        }

        $this->countries = Country::all();
    }

    public function updatedSelectedCountry(): void
    {
        // A new country means the old category choice no longer applies.
        $this->selectedCategory = null;
    }

    public function render()
    {
        $this->categories = Category::orderBy('name')->get();

        return view('livewire.filter-section', [
            'setting' => Setting::first(),
        ]);
    }
    // Other methods go here

    public function updateSelectedCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;

        // A category tap is the final step: persist and open the listing
        // right away instead of waiting for a form submit.
        $this->filterValues();
    }

    public function filterValues()
    {
        $this->validate();

        if (auth()->check()) {
            $userId = auth()->user()->id;
            $existingValue = FilterValue::where('user_id', $userId)->first();

            $data = [
                'user_id' => $userId,
                'country_id' => $this->selectedCountry,
                'state_id' => null,
                'city_id' => null,
                'category_id' => $this->selectedCategory,
            ];

            if ($existingValue) {
                $existingValue->update($data);
            } else {
                FilterValue::create($data);
            }
        } else {
            // If the user is not authenticated, store filter values in the session
            session([
                'filter_values' => [
                    'country_id' => $this->selectedCountry,
                    'state_id' => null,
                    'city_id' => null,
                    'category_id' => $this->selectedCategory,
                ],
            ]);
        }

        return $this->redirect($this->listingUrl(), navigate: true);
    }

    private function listingUrl(): string
    {
        $country = Country::find($this->selectedCountry);
        $category = Category::find($this->selectedCategory);

        if (! $country || ! $category) {
            return '/';
        }

        return route('artisans.index', [
            'country' => $country->slug ?: Str::slug($country->name),
            'category' => $category->slug ?: Str::slug($category->name),
        ]);
    }
}
