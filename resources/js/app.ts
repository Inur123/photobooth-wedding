import '@/lib/polyfills';
import '../css/app.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'titip.moment';

void createInertiaApp({
    title: (title) => {
        if (!title) {
            return appName;
        }

        return title.toLowerCase().includes(appName.toLowerCase())
            ? title
            : `${title} — ${appName}`;
    },
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name === 'TermsOfService':
            case name === 'PrivacyPolicy':
            case name === 'Error':
                return null;
            case name.startsWith('event/'):
                return null;
            case name.startsWith('auth/'):
                return null;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#d92c3f',
    },
});

initializeTheme();
initializeFlashToast();
