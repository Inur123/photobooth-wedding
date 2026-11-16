<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const name = (usePage().props.name as string) || 'titip.moment';
</script>

<template>
    <div
        class="flex items-center justify-center gap-2.5 group-data-[collapsible=icon]:w-full"
    >
        <img
            src="/images/titip-momen-logo-web.png"
            :alt="name"
            width="112"
            height="32"
            class="h-7 w-auto object-contain group-data-[collapsible=icon]:hidden"
        />
        <div
            class="hidden size-8 items-center justify-center p-1 group-data-[collapsible=icon]:flex"
        >
            <img
                src="/images/titip-momen-logo-web.png"
                :alt="name"
                class="max-h-5 w-full object-contain"
            />
        </div>
    </div>
</template>
