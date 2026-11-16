<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Lock, Mail, User } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();
</script>

<template>
    <AuthLayout
        current-tab="register"
        title="Buat Akun Event Baru"
        description="Mulai setup photobooth estetik untuk acaramu."
    >
        <Head title="Daftar Akun — titip.moment" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="space-y-3.5"
        >
            <!-- Full Name -->
            <div class="space-y-1.5">
                <Label for="name" class="block text-xs font-bold text-on-surface">
                    Nama Lengkap / Penyelenggara
                </Label>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-on-surface-variant"
                    >
                        <User class="h-4 w-4" />
                    </div>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Contoh: Nadia Safira"
                        class="h-11 w-full rounded-xl border-outline-variant/60 bg-surface/50 pl-10 pr-4 text-sm text-on-surface transition-all outline-hidden focus:border-primary-container focus:ring-2 focus:ring-primary-container/20"
                    />
                </div>
                <InputError :message="errors.name" />
            </div>

            <!-- Email Input -->
            <div class="space-y-1.5">
                <Label for="email" class="block text-xs font-bold text-on-surface">
                    Email Aktif
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
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="nama@email.com"
                        class="h-11 w-full rounded-xl border-outline-variant/60 bg-surface/50 pl-10 pr-4 text-sm text-on-surface transition-all outline-hidden focus:border-primary-container focus:ring-2 focus:ring-primary-container/20"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <!-- Password Input -->
            <div class="space-y-1.5">
                <Label for="password" class="block text-xs font-bold text-on-surface">
                    Kata Sandi
                </Label>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3.5 text-on-surface-variant"
                    >
                        <Lock class="h-4 w-4" />
                    </div>
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Minimal 8 karakter"
                        class="h-11 w-full rounded-xl border-outline-variant/60 bg-surface/50 pl-10 pr-10 text-sm text-on-surface transition-all outline-hidden focus:border-primary-container focus:ring-2 focus:ring-primary-container/20"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <!-- Confirm Password Input -->
            <div class="space-y-1.5">
                <Label
                    for="password_confirmation"
                    class="block text-xs font-bold text-on-surface"
                >
                    Konfirmasi Kata Sandi
                </Label>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3.5 text-on-surface-variant"
                    >
                        <Lock class="h-4 w-4" />
                    </div>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Ulangi kata sandi"
                        class="h-11 w-full rounded-xl border-outline-variant/60 bg-surface/50 pl-10 pr-10 text-sm text-on-surface transition-all outline-hidden focus:border-primary-container focus:ring-2 focus:ring-primary-container/20"
                    />
                </div>
                <InputError :message="errors.password_confirmation" />
            </div>

            <!-- Terms Notice -->
            <p class="pt-0.5 text-center text-xs leading-normal text-on-surface-variant">
                Dengan mendaftar, kamu menyetujui<br />
                <Link
                    href="/terms"
                    class="font-semibold text-primary hover:underline whitespace-nowrap"
                >
                    Ketentuan Layanan
                </Link>
                &amp;
                <Link
                    href="/privacy"
                    class="font-semibold text-primary hover:underline whitespace-nowrap"
                >
                    Kebijakan Privasi
                </Link>
            </p>

            <!-- Submit Button -->
            <Button
                type="submit"
                class="coral-button-gradient h-11 w-full rounded-full text-sm font-bold text-white transition-all duration-200"
                :tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                <span v-else>Buat Akun Event</span>
            </Button>

            <div
                class="border-t border-outline-variant/40 pt-3 text-center text-xs text-on-surface-variant"
            >
                Sudah punya akun?
                <TextLink
                    :href="login()"
                    class="font-bold text-primary hover:underline"
                    :tabindex="6"
                >
                    Masuk di sini
                </TextLink>
            </div>
        </Form>
    </AuthLayout>
</template>
