@props([
    'url',
    'title',
])

<div x-data="shareMenu('{{ $url }}', '{{ addslashes($title) }}')" @click.outside="open = false" @keydown.escape.window="open = false" class="relative inline-flex">
    <button type="button" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full bg-slate-900 text-white transition hover:bg-slate-700']) }} @click="open = true" aria-label="Share {{ $title }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="18" cy="5" r="2"/><circle cx="6" cy="12" r="2"/><circle cx="18" cy="19" r="2"/><path stroke-linecap="round" d="m8 11 8-5m-8 7 8 5"/></svg>
    </button>

    <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 sm:items-center sm:p-6" @click.self="open = false" role="dialog" aria-modal="true" aria-label="Share options">
        <div class="relative w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-md sm:rounded-2xl">
            <div class="mb-1 flex items-center justify-between gap-3">
                <h3 class="text-base font-bold text-slate-900">Share this profile</h3>
                <button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200" @click="open = false" aria-label="Close share options">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <p class="mb-4 truncate text-xs font-semibold text-slate-500">{{ $title }}</p>

            <div class="grid grid-cols-4 gap-3">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($url) }}" target="_blank" rel="noopener" @click="open = false" class="flex flex-col items-center gap-1.5 rounded-xl p-2 transition hover:bg-slate-100">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1877F2] text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8.2h2.8l.4-3.2h-3.2V7.6c0-.9.3-1.6 1.6-1.6H17V3.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.3H7.5v3.2h2.8V21h3.2Z"/></svg>
                    </span>
                    <span class="text-[11px] font-bold text-slate-700">Facebook</span>
                </a>

                <a href="https://wa.me/?text={{ rawurlencode($title.' '.$url) }}" target="_blank" rel="noopener" @click="open = false" class="flex flex-col items-center gap-1.5 rounded-xl p-2 transition hover:bg-slate-100">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#25D366] text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.8 14.2c-.25.7-1.45 1.33-2 1.38-.52.05-1.17.07-1.9-.12a16 16 0 0 1-1.72-.63c-3.03-1.3-5-4.35-5.15-4.55-.15-.2-1.23-1.63-1.23-3.11 0-1.48.78-2.2 1.05-2.5.28-.3.6-.38.8-.38h.58c.18 0 .43-.07.67.5.25.6.85 2.07.92 2.22.08.15.13.33.03.53-.1.2-.15.32-.3.5-.15.17-.31.38-.45.51-.15.15-.3.31-.13.6.18.3.78 1.28 1.67 2.07 1.15 1.02 2.11 1.34 2.41 1.49.3.15.47.12.65-.08.17-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.28.1 1.75.83 2.05.98.3.15.5.22.57.35.08.12.08.72-.17 1.42Z"/></svg>
                    </span>
                    <span class="text-[11px] font-bold text-slate-700">WhatsApp</span>
                </a>

                <a href="https://twitter.com/intent/tweet?text={{ rawurlencode($title) }}&amp;url={{ rawurlencode($url) }}" target="_blank" rel="noopener" @click="open = false" class="flex flex-col items-center gap-1.5 rounded-xl p-2 transition hover:bg-slate-100">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-900 text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.7 3H21l-7.1 8.2L22 21h-6.6l-5.1-6.1L4.4 21H1l7.6-8.7L2 3h6.7l4.6 5.6L17.7 3Zm-1.1 16h1.8L7.7 4.9H5.8l10.8 14.1Z"/></svg>
                    </span>
                    <span class="text-[11px] font-bold text-slate-700">X</span>
                </a>

                <a href="https://t.me/share/url?url={{ rawurlencode($url) }}&amp;text={{ rawurlencode($title) }}" target="_blank" rel="noopener" @click="open = false" class="flex flex-col items-center gap-1.5 rounded-xl p-2 transition hover:bg-slate-100">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#229ED9] text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 3 2 11l6 2 2 6 4-5 5 4 3-15Z"/></svg>
                    </span>
                    <span class="text-[11px] font-bold text-slate-700">Telegram</span>
                </a>

                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ rawurlencode($url) }}" target="_blank" rel="noopener" @click="open = false" class="flex flex-col items-center gap-1.5 rounded-xl p-2 transition hover:bg-slate-100">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#0A66C2] text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.5 8.5v10H3.2v-10h3.3Zm.2-3.2c0 1-.8 1.8-1.8 1.8s-1.8-.8-1.8-1.8.8-1.8 1.8-1.8 1.8.8 1.8 1.8Zm14 6.4v6.8h-3.3v-6c0-1.4-.5-2.3-1.7-2.3-1 0-1.5.6-1.8 1.3-.1.2-.1.6-.1.9v6.1H10.5v-10h3.3v1.4c.4-.7 1.2-1.7 3-1.7 2.2 0 3.9 1.4 3.9 4.5Z"/></svg>
                    </span>
                    <span class="text-[11px] font-bold text-slate-700">LinkedIn</span>
                </a>

                <a href="mailto:?subject={{ rawurlencode($title) }}&amp;body={{ rawurlencode($url) }}" @click="open = false" class="flex flex-col items-center gap-1.5 rounded-xl p-2 transition hover:bg-slate-100">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-sky-500 text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" d="m3 7 9 6 9-6"/></svg>
                    </span>
                    <span class="text-[11px] font-bold text-slate-700">Email</span>
                </a>

                <a href="sms:?&amp;body={{ rawurlencode($title.' '.$url) }}" @click="open = false" class="flex flex-col items-center gap-1.5 rounded-xl p-2 transition hover:bg-slate-100">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-600 text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3h.01M8 5h.01M16 5h.01M8 9h.01M16 9h.01M12 7h.01M5 13h14a4 4 0 0 1 4 4v1a3 3 0 0 1-3 3H4a3 3 0 0 1-3-3v-1a4 4 0 0 1 4-4Z"/></svg>
                    </span>
                    <span class="text-[11px] font-bold text-slate-700">SMS</span>
                </a>

                <button type="button" class="flex flex-col items-center gap-1.5 rounded-xl p-2 transition hover:bg-slate-100" @click="copyLink()">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full text-white transition" :class="copied ? 'bg-emerald-500' : 'bg-slate-700'">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg>
                    </span>
                    <span class="text-[11px] font-bold text-slate-700" x-text="copied ? 'Copied!' : 'Copy link'"></span>
                </button>
            </div>

            <button type="button" x-show="!! navigator.share" x-cloak @click="shareNative()" class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7 7-7 7 7"/></svg>
                More apps
            </button>
        </div>
    </div>
</div>
