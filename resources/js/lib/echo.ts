import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Check if WebSocket is enabled via environment variable
const isWebSocketEnabled = import.meta.env.VITE_ENABLE_WEBSOCKET === 'true';

let echo: Echo<any> | null = null;

// Only initialize Echo if WebSocket is enabled
if (isWebSocketEnabled) {
    // Make Pusher globally available for Laravel Echo
    (window as any).Pusher = Pusher;

    // Create and configure Echo instance
    echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY || 'local',
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
        wsHost: import.meta.env.VITE_PUSHER_HOST || '127.0.0.1',
        wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
        wssPort: import.meta.env.VITE_PUSHER_PORT || 6001,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME || 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
        },
    });
} else {
    console.info('WebSocket is disabled. Set VITE_ENABLE_WEBSOCKET=true to enable.');
}

export default echo;
