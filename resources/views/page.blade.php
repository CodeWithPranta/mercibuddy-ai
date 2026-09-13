@extends('layouts.landing')
@section('content')

<livewire:navigation />

<div class="app-page">
    <div class="app-page-card">
        <h1 class="app-page-title">{{ $page->title }}</h1>
        <div class="app-page-content">
            {!! $page->content !!}
        </div>
    </div>
</div>

<livewire:footer-section />
@endsection
