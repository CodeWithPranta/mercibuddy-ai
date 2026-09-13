@extends('layouts.landing')
@section('content')

    <livewire:navigation />
    <div class="app-results-wrap">
        <livewire:filtered-artisan :country-id="$country->id" :category-id="$category->id" />
    </div>

    <livewire:footer-section />
@endsection
