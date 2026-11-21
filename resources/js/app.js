import './bootstrap';

import 'flowbite';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('shareMenu', (url, title) => ({
        open: false,
        copied: false,
        url,
        title,

        async copyLink() {
            this.copied = false;

            let success = false;

            if (window.isSecureContext && navigator.clipboard) {
                try {
                    await navigator.clipboard.writeText(this.url);
                    success = true;
                } catch (error) {
                    success = false;
                }
            }

            if (!success) {
                success = this.copyLinkLegacy();
            }

            this.copied = success;

            if (success) {
                setTimeout(() => {
                    this.copied = false;
                }, 2000);
            }
        },

        // Fallback for non-secure contexts (plain HTTP) where the
        // async Clipboard API is unavailable.
        copyLinkLegacy() {
            const helper = document.createElement('textarea');
            helper.value = this.url;
            helper.setAttribute('readonly', '');
            helper.style.position = 'absolute';
            helper.style.left = '-9999px';
            document.body.appendChild(helper);

            if (/ipad|ipod|iphone/i.test(navigator.userAgent)) {
                const range = document.createRange();
                range.selectNodeContents(helper);
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
                helper.setSelectionRange(0, 999999);
            } else {
                helper.select();
            }

            let success = false;

            try {
                success = document.execCommand('copy');
            } catch (error) {
                success = false;
            }

            document.body.removeChild(helper);

            return success;
        },

        shareNative() {
            if (navigator.share) {
                navigator.share({ title: this.title, url: this.url });
            }

            this.open = false;
        },
    }));
});

function scrollChatToBottom() {
    queueMicrotask(() => {
        const chatBody = document.getElementById('app-chat-body');

        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    });
}

document.addEventListener('livewire:init', () => {
    window.Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            scrollChatToBottom();
        });
    });
});

document.addEventListener('livewire:navigated', () => {
    scrollChatToBottom();
});

// Browsers can restore a cached landing page (back/forward or refresh) with
// the previously tapped category still highlighted. The server state is
// intentionally reset on mount, so clear the stale visual state too.
function clearCategorySelection() {
    document.querySelectorAll('.app-category-selected').forEach((button) => {
        button.classList.remove('app-category-selected');
        button.setAttribute('aria-pressed', 'false');
    });
}

window.addEventListener('pageshow', () => {
    window.setTimeout(clearCategorySelection, 0);
});

document.addEventListener('livewire:navigated', () => {
    window.setTimeout(clearCategorySelection, 0);
});
