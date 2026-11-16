import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

let lastToastTime = 0;
let lastToastMessage = '';

export function notifyAuthError(message: string): void {
    const trimmed = typeof message === 'string' ? message.trim() : '';
    if (!trimmed) {
        return;
    }

    const now = Date.now();
    if (trimmed === lastToastMessage && now - lastToastTime < 1000) {
        return;
    }

    lastToastMessage = trimmed;
    lastToastTime = now;
    toast.error(trimmed);
}

export function initializeFlashToast(): void {
    router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;
        const data = flash?.toast as FlashToast | undefined;

        if (!data) {
            return;
        }

        toast[data.type](data.message);
    });

    router.on('error', (event) => {
        const detail = (event as CustomEvent).detail;
        const errors = detail?.errors as Record<string, string> | undefined;
        if (!errors || Object.keys(errors).length === 0) {
            return;
        }

        const page = detail?.page ?? (router as any).page;
        const component = page?.component || '';
        const url = page?.url || '';
        const isAuth =
            component.startsWith('auth/') ||
            ['/login', '/register', '/forgot-password', '/reset-password'].some(
                (p) => url.startsWith(p),
            );

        if (isAuth) {
            const uniqueMessages = Array.from(new Set(Object.values(errors)));
            for (const message of uniqueMessages) {
                notifyAuthError(message);
            }
        }
    });
}
