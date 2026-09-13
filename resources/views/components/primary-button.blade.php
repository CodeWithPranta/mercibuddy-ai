<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-blue-700 hover:bg-blue-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest dark:hover:bg-blue-800 focus:bg-blue-800 dark:focus:bg-blue-800 active:bg-blue-900 dark:active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-800 focus:ring-offset-2 dark:focus:ring-offset-indigo-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
