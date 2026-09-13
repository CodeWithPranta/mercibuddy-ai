import './bootstrap';

import 'flowbite';

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
