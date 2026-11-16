import type { ComputedRef, Ref } from 'vue';
import { computed, ref } from 'vue';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

export function updateTheme(_value?: Appearance): void {
    if (typeof window === 'undefined') return;
    document.documentElement.classList.remove('dark');
    document.documentElement.style.colorScheme = 'light';
    if (document.body) {
        document.body.classList.remove('dark');
        document.body.style.colorScheme = 'light';
    }
}

export function initializeTheme(): void {
    if (typeof window === 'undefined') return;
    localStorage.removeItem('appearance');
    document.cookie = 'appearance=light;path=/;max-age=31536000;SameSite=Lax';
    updateTheme();

    if (typeof MutationObserver !== 'undefined' && document.documentElement) {
        const observer = new MutationObserver(() => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
            }
            if (document.body?.classList.contains('dark')) {
                document.body.classList.remove('dark');
            }
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });
    }
}

const appearance = ref<Appearance>('light');

export function useAppearance(): UseAppearanceReturn {
    const resolvedAppearance = computed<ResolvedAppearance>(() => 'light');

    function updateAppearance(_value: Appearance) {
        appearance.value = 'light';
        updateTheme();
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
