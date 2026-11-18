<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Lock, Mail } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { update } from '@/routes/password';

const props = defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <AuthLayout
        current-tab="reset"
        title="Buat Kata Sandi Baru"
        description="Masukkan kata sandi baru untuk akun penyelenggara acaramu."
    >
        <Head title="Atur Ulang Kata Sandi — titip.moment" />

        <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="space-y-4"
        >
            <div class="space-y-1">
                <Label
                    for="email"
                    class="block text-xs font-bold text-on-surface"
                >
                    Alamat Email
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
                        autocomplete="email"
                        v-model="inputEmail"
                        class="h-11 w-full rounded-xl border-outline-variant/60 bg-surface-container-low pr-4 pl-10 text-sm text-on-surface-variant outline-hidden"
                        readonly
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <div class="space-y-1">
                <Label
                    for="password"
                    class="block text-xs font-bold text-on-surface"
                >
                    Kata Sandi Baru
                </Label>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3.5 text-on-surface-variant"
                    >
                        <Lock class="h-4 w-4" />
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        autofocus
                        placeholder="Minimal 8 karakter"
                        class="h-11 w-full rounded-xl border-outline-variant/60 bg-surface/50 pr-10 pl-10 text-sm text-on-surface outline-hidden transition-all focus:border-primary-container focus:ring-2 focus:ring-primary-container/20"
                    />
                </div>
                <p class="text-[11px] text-on-surface-variant">
                    Minimal 8 karakter.
                </p>
                <InputError :message="errors.password" />
            </div>

            <div class="space-y-1">
                <Label
                    for="password_confirmation"
                    class="block text-xs font-bold text-on-surface"
                >
                    Konfirmasi Kata Sandi Baru
                </Label>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3.5 text-on-surface-variant"
                    >
                        <Lock class="h-4 w-4" />
                    </div>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi baru"
                        class="h-11 w-full rounded-xl border-outline-variant/60 bg-surface/50 pr-10 pl-10 text-sm text-on-surface outline-hidden transition-all focus:border-primary-container focus:ring-2 focus:ring-primary-container/20"
                    />
                </div>
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="coral-button-gradient h-11 w-full rounded-full text-sm font-bold text-white transition-all duration-200"
                :disabled="processing"
                data-test="reset-password-button"
            >
                <Spinner v-if="processing" />
                <span v-else>Simpan Kata Sandi Baru</span>
            </Button>
        </Form>
    </AuthLayout>
</template>
