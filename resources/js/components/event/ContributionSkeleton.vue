<script setup lang="ts">
withDefaults(
    defineProps<{
        dominantColor?: string | null;
    }>(),
    {
        dominantColor: null,
    },
);
</script>

<template>
    <div
        class="contribution-skeleton"
        :style="dominantColor ? { '--sk-color': dominantColor } : {}"
        aria-hidden="true"
    >
        <div class="sk-header">
            <div class="sk-avatar" />
            <div class="sk-meta">
                <div class="sk-line sk-line--name" />
                <div class="sk-line sk-line--date" />
            </div>
        </div>
        <div class="sk-media" />
        <div class="sk-line sk-line--message" />
    </div>
</template>

<style scoped>
.contribution-skeleton {
    --sk-color: var(--event-soft, #f5ece8);
    background: var(--event-surface, #fff);
    border-radius: 12px;
    padding: 1rem;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.sk-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sk-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--sk-color);
    flex-shrink: 0;
    animation: shimmer 1.5s infinite;
    background-size: 200% 100%;
    background-image: linear-gradient(
        90deg,
        var(--sk-color) 25%,
        color-mix(in srgb, var(--sk-color) 60%, white) 50%,
        var(--sk-color) 75%
    );
}

.sk-meta {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.sk-line {
    border-radius: 4px;
    animation: shimmer 1.5s infinite;
    background-size: 200% 100%;
    background-image: linear-gradient(
        90deg,
        var(--sk-color) 25%,
        color-mix(in srgb, var(--sk-color) 60%, white) 50%,
        var(--sk-color) 75%
    );
}

.sk-line--name {
    height: 12px;
    width: 55%;
}

.sk-line--date {
    height: 10px;
    width: 35%;
}

.sk-media {
    aspect-ratio: 4 / 3;
    border-radius: 8px;
    background-image: linear-gradient(
        90deg,
        var(--sk-color) 25%,
        color-mix(in srgb, var(--sk-color) 60%, white) 50%,
        var(--sk-color) 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

.sk-line--message {
    height: 10px;
    width: 75%;
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}
</style>
