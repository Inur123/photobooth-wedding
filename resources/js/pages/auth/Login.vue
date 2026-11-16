<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Lock, Mail } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <AuthLayout
        current-tab="login"
        title="Selamat Datang Kembali!"
        description="Masuk untuk mengelola galeri, tema kartu & QR photobooth acaramu."
    >
        <Head title="Masuk — titip.moment" />

        <div
            v-if="status"
            role="status"
            class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-900"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="space-y-4"
        >
            <!-- Email Input -->
            <div class="space-y-1.5">
                <Label for="email" class="block text-xs font-bold text-on-surface">
                    Email
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
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="nama@email.com"
                        class="h-11 w-full rounded-xl border-outline-variant/60 bg-surface/50 pl-10 pr-4 text-sm text-on-surface transition-all outline-hidden focus:border-primary-container focus:ring-2 focus:ring-primary-container/20"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <!-- Password Input -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <Label for="password" class="block text-xs font-bold text-on-surface">
                        Kata Sandi
                    </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs font-semibold text-primary hover:text-primary-container"
                        :tabindex="5"
                    >
                        Lupa kata sandi?
                    </TextLink>
                </div>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3.5 text-on-surface-variant"
                    >
                        <Lock class="h-4 w-4" />
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi"
                        class="h-11 w-full rounded-xl border-outline-variant/60 bg-surface/50 pl-10 pr-10 text-sm text-on-surface transition-all outline-hidden focus:border-primary-container focus:ring-2 focus:ring-primary-container/20"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center pt-0.5">
                <Checkbox id="remember" name="remember" :tabindex="3" />
                <Label
                    for="remember"
                    class="ml-2 cursor-pointer text-xs font-medium text-on-surface-variant"
                >
                    Ingat saya di perangkat ini
                </Label>
            </div>

            <!-- Submit Button -->
            <Button
                type="submit"
                class="coral-button-gradient h-11 w-full rounded-full text-sm font-bold text-white transition-all duration-200"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                <span v-else>Masuk ke Akun</span>
            </Button>

            <div
                class="border-t border-outline-variant/40 pt-3 text-center text-xs text-on-surface-variant"
            >
                Belum punya akun event?
                <TextLink
                    :href="register()"
                    :tabindex="6"
                    class="font-bold text-primary hover:underline"
                >
                    Daftar di sini
                </TextLink>
            </div>
        </Form>
    </AuthLayout>
</template>
