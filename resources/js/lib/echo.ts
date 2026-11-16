import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo?: Echo<'reverb'>;
    }
}

window.Pusher = Pusher;

let echoInstance: Echo<'reverb'> | null = null;

export function getEcho(): Echo<'reverb'> | null {
    if (typeof window === 'undefined') {
        return null;
    }

    if (echoInstance) {
        return echoInstance;
    }

    const key = import.meta.env.VITE_REVERB_APP_KEY;
    if (!key) {
        return null;
    }

    const envHost = import.meta.env.VITE_REVERB_HOST;
    const wsHost = envHost && envHost !== 'localhost' ? envHost : window.location.hostname;
    const isHttps = window.location.protocol === 'https:';
    const forceTLS = (import.meta.env.VITE_REVERB_SCHEME ?? (isHttps ? 'https' : 'http')) === 'https' || isHttps;
    const port = Number(import.meta.env.VITE_REVERB_PORT) || (forceTLS ? 443 : 8080);

    echoInstance = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost,
        wsPort: port,
        wssPort: port,
        forceTLS,
        enabledTransports: ['ws', 'wss'],
    });

    window.Echo = echoInstance;
    return echoInstance;
}
