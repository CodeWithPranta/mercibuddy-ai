<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#0f5132] hover:bg-[#0b3d26] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:bg-[#0b3d26] active:bg-[#082d1c] focus:outline-none focus:ring-2 focus:ring-[#0f5132] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
