<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

declare global {
    interface Window {
        turnstile?: {
            render: (
                container: HTMLElement | string,
                options: Record<string, unknown>,
            ) => string;
            reset: (widgetId?: string) => void;
            remove: (widgetId?: string) => void;
        };
        onloadTurnstileCallback?: () => void;
    }
}

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        error?: string;
        theme?: 'auto' | 'light' | 'dark';
        size?: 'normal' | 'compact' | 'flexible';
    }>(),
    {
        modelValue: '',
        error: '',
        theme: 'auto',
        size: 'normal',
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const siteKey =
    (import.meta.env.VITE_TURNSTILE_SITE_KEY as string | undefined) || '';
const containerRef = ref<HTMLElement | null>(null);
const widgetId = ref<string | null>(null);
const token = ref(props.modelValue);
const isLoaded = ref(false);

let observer: MutationObserver | null = null;
let fallbackTimeout: ReturnType<typeof setTimeout> | null = null;

function markLoaded(): void {
    if (!isLoaded.value) {
        isLoaded.value = true;
    }
}

function observeContainer(): void {
    if (!containerRef.value) {
        return;
    }

    const existingIframe = containerRef.value.querySelector('iframe');
    if (existingIframe) {
        existingIframe.addEventListener('load', markLoaded, { once: true });
        setTimeout(markLoaded, 250);
        return;
    }

    if (observer) {
        observer.disconnect();
    }

    observer = new MutationObserver(() => {
        if (!containerRef.value) {
            return;
        }
        const iframe = containerRef.value.querySelector('iframe');
        if (iframe) {
            iframe.addEventListener('load', markLoaded, { once: true });
            setTimeout(markLoaded, 250);
            if (observer) {
                observer.disconnect();
                observer = null;
            }
        }
    });

    observer.observe(containerRef.value, { childList: true, subtree: true });
}

function renderTurnstile(): void {
    if (!siteKey || !containerRef.value || !window.turnstile) {
        return;
    }

    if (widgetId.value) {
        try {
            window.turnstile.remove(widgetId.value);
        } catch {
            // Widget might already be cleared
        }
        widgetId.value = null;
    }

    observeContainer();

    if (fallbackTimeout) {
        clearTimeout(fallbackTimeout);
    }
    fallbackTimeout = setTimeout(() => {
        markLoaded();
    }, 2500);

    widgetId.value = window.turnstile.render(containerRef.value, {
        sitekey: siteKey,
        theme: props.theme,
        size: props.size,
        callback: (responseToken: string) => {
            token.value = responseToken;
            emit('update:modelValue', responseToken);
            markLoaded();
        },
        'before-interactive-callback': () => {
            markLoaded();
        },
        'after-interactive-callback': () => {
            markLoaded();
        },
        'error-callback': () => {
            token.value = '';
            emit('update:modelValue', '');
            markLoaded();
        },
        'expired-callback': () => {
            token.value = '';
            emit('update:modelValue', '');
        },
    });
}

function loadTurnstileScript(): void {
    if (!siteKey) {
        return;
    }

    if (window.turnstile) {
        nextTick(() => renderTurnstile());
        return;
    }

    const scriptId = 'cloudflare-turnstile-script';
    let script = document.getElementById(scriptId) as HTMLScriptElement | null;

    if (!script) {
        script = document.createElement('script');
        script.id = scriptId;
        script.src =
            'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback&render=explicit';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }

    const prevCallback = window.onloadTurnstileCallback;
    window.onloadTurnstileCallback = () => {
        if (typeof prevCallback === 'function') {
            prevCallback();
        }
        renderTurnstile();
    };
}

function reset(): void {
    if (widgetId.value && window.turnstile) {
        try {
            window.turnstile.reset(widgetId.value);
        } catch {
            // Safe fallback
        }
    }
    token.value = '';
    emit('update:modelValue', '');
}

watch(
    () => props.error,
    (newError) => {
        if (newError) {
            reset();
        }
    },
);

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal !== token.value) {
            token.value = newVal;
        }
    },
);

onMounted(() => {
    loadTurnstileScript();
});

onBeforeUnmount(() => {
    if (observer) {
        observer.disconnect();
        observer = null;
    }
    if (fallbackTimeout) {
        clearTimeout(fallbackTimeout);
        fallbackTimeout = null;
    }
    if (widgetId.value && window.turnstile) {
        try {
            window.turnstile.remove(widgetId.value);
        } catch {
            // Widget might already be destroyed
        }
        widgetId.value = null;
    }
});

defineExpose({
    reset,
});
</script>

<template>
    <div
        v-if="siteKey"
        class="flex w-full flex-col items-start justify-start py-0.5"
    >
        <div class="relative min-h-[65px] w-[300px]">
            <!-- Skeleton Loader matching Turnstile shape -->
            <div
                v-if="!isLoaded"
                class="pointer-events-none absolute inset-0 z-10 flex h-[65px] w-[300px] items-center justify-between rounded-md border border-neutral-300/80 bg-[#f9f9f9] px-3.5 py-3 shadow-xs transition-opacity duration-200 select-none"
                aria-hidden="true"
            >
                <div class="flex items-center gap-3">
                    <!-- Square Checkbox Placeholder -->
                    <div
                        class="size-7 shrink-0 animate-pulse rounded-sm bg-neutral-200/90"
                    />
                    <!-- Text lines -->
                    <div class="flex flex-col gap-1.5">
                        <div
                            class="h-3.5 w-28 animate-pulse rounded-full bg-neutral-200/90"
                        />
                        <div
                            class="h-2 w-16 animate-pulse rounded-full bg-neutral-200/60"
                        />
                    </div>
                </div>

                <!-- Right Side Cloudflare Logo Placeholder -->
                <div class="flex flex-col items-center gap-1">
                    <div
                        class="size-6 animate-pulse rounded-sm bg-neutral-200/80"
                    />
                    <div
                        class="h-1.5 w-6 animate-pulse rounded-full bg-neutral-200/50"
                    />
                </div>
            </div>

            <!-- Turnstile Widget Container -->
            <div
                ref="containerRef"
                class="flex min-h-[65px] w-[300px] items-center justify-start transition-opacity duration-200"
                :class="isLoaded ? 'opacity-100' : 'opacity-0'"
            ></div>
        </div>

        <input type="hidden" name="cf-turnstile-response" :value="token" />
    </div>
</template>
