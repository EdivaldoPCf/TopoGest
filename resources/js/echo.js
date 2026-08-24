import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const wsPort = Number(window.location.port || (window.location.protocol === 'https:' ? 443 : 80));

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort,
    wssPort: wsPort,
    wsPath: '/topogest',
    forceTLS: window.location.protocol === 'https:',
    enabledTransports: ['ws', 'wss'],
});
