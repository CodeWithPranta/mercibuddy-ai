<section class="app-content">
    <div class="app-content-inner">
        <div class="app-hero">
            <p class="app-eyebrow">Trusted local services</p>
            <h1 class="app-heading">Find skilled artisans <span>near you</span></h1>
        </div>
        <form wire:submit.prevent="filterValues" class="app-search-card">
            <div class="app-location-field">
                <span class="app-globe" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M3.5 12h17M12 3.5c2.3 2.4 3.4 5.3 3.4 8.5s-1.1 6.1-3.4 8.5c-2.3-2.4-3.4-5.3-3.4-8.5S9.7 5.9 12 3.5Z" /></svg>
                </span>
                <label for="country" class="sr-only">Country</label>
                <select wire:model.live="selectedCountry" id="country" autocomplete="on" class="app-country-select">
                    <option value="">Select your country</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-input-error :messages="$errors->get('selectedCountry')" class="app-error" />
            <div class="app-category-head">
                <h2>Browse categories</h2>
                <span>{{ $categories->count() }} crafts</span>
            </div>
            <div class="app-category-scroll" tabindex="0" aria-label="Scrollable category list">
                <div class="app-category-list">
                    @foreach ($categories as $category)
                        <button type="button" wire:click="updateSelectedCategory('{{ $category->id }}')" class="app-category-button {{ (string) $selectedCategory === (string) $category->id ? 'app-category-selected' : '' }}" aria-pressed="{{ (string) $selectedCategory === (string) $category->id ? 'true' : 'false' }}">
                            <span class="app-category-icon" aria-hidden="true">{!! $category->icon ?: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-6-6h12"/></svg>' !!}</span>
                            <span class="app-category-name">{{ $category->name }}</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" /></svg>
                        </button>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="hidden"></button>
        </form>
    </div>
</section>
