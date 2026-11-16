<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { HelpCircle, Mail } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { email } from '@/routes/password';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        current-tab="forgot"
        title="Reset Kata Sandi"
        description="Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan aman untuk membuat kata sandi baru."
    >
        <Head title="Lupa Kata Sandi — titip.moment" />

        <div
            v-if="status"
            role="status"
            class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-900"
        >
            {{ status }}
        </div>

        <Form
            v-bind="email.form()"
            v-slot="{ errors, processing }"
            class="space-y-4"
        >
            <div class="space-y-1.5">
                <Label
                    for="email"
                    class="block text-xs font-bold text-on-surface"
                >
                    Email Terdaftar
                </Label>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-on-surface-variant"
                    >
                        <Mail class="h-4 w-4" />
                    </div>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        autofocus
                        placeholder="nama@email.com"
                        class="h-11 w-full rounded-xl border-outline-variant/60 bg-surface/50 pr-4 pl-10 text-sm text-on-surface outline-hidden transition-all focus:border-primary-container focus:ring-2 focus:ring-primary-container/20"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <Button
                type="submit"
                class="coral-button-gradient h-11 w-full rounded-full text-sm font-bold text-white transition-all duration-200"
                :disabled="processing"
                data-test="email-password-reset-link-button"
            >
                <Spinner v-if="processing" />
                <span v-else>Kirim Link Reset</span>
            </Button>

            <div
                class="flex items-start gap-3 rounded-xl border border-outline-variant/30 bg-surface-container-low p-3.5"
            >
                <HelpCircle class="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                <div class="space-y-0.5 text-xs">
                    <p class="font-bold text-on-surface">
                        Butuh bantuan saat hari-H?
                    </p>
                    <p class="text-on-surface-variant">
                        Hubungi tim kami di
                        <a
                            href="mailto:kontak@titipmoment.id"
                            class="font-semibold text-primary underline"
                        >
                            kontak@titipmoment.id
                        </a>
                    </p>
                </div>
            </div>
        </Form>
    </AuthLayout>
</template>
