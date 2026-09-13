@if (Session::has('message'))
    <div x-data="{ show: true }"
        x-init="setTimeout(() => { show = false; }, 5000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90"
        class="fixed top-20 right-4 z-50 w-64 rounded-lg bg-green-100 px-6 py-5 text-base text-green-700"
        role="alert">
        {{ Session::get('message') }}
    </div>
@endif
