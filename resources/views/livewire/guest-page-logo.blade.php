<div>
    @php
        $logo = $setting?->logo;
        $logoUrl = $logo
            ? (file_exists(public_path('photos/'.$logo))
                ? asset('photos/'.$logo)
                : \Illuminate\Support\Facades\Storage::disk('public')->url($logo))
            : null;
    @endphp
    @if ($logoUrl)
        <x-custom-logo src="{{ $logoUrl }}" class="h-7 sm:h-8 w-auto" />
    @else
        <p class="app-brand">merci<span>buddy</span></p>
    @endif
</div>
