<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { CheckCircle2, MailCheck, RefreshCw } from '@lucide/vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
    email?: string | null;
}>();
</script>

<template>
    <AuthLayout
        current-tab="verify"
        title="Cek Inbox Emailmu"
        description="Satu langkah lagi untuk mengaktifkan photobooth digital acaramu."
    >
        <Head title="Verifikasi Email — titip.moment" />

        <div class="space-y-4 text-center">
            <!-- Envelope Illustration Container -->
            <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-primary/20 bg-rose-50 text-primary shadow-inner"
            >
                <MailCheck class="h-8 w-8" />
            </div>

            <p class="text-xs leading-relaxed text-on-surface-variant sm:text-sm">
                Kami telah mengirim tautan verifikasi akun ke:
                <br />
                <strong class="font-bold text-on-surface">{{ email || 'alamat email terdaftar' }}</strong>
            </p>

            <div
                class="space-y-2 rounded-xl border border-outline-variant/40 bg-surface-container-low p-3.5 text-left text-xs text-on-surface-variant"
            >
                <div class="flex items-center gap-2">
                    <CheckCircle2 class="h-3.5 w-3.5 shrink-0 text-emerald-600" />
                    <span>Periksa folder Spam atau Promosi jika belum muncul.</span>
                </div>
                <div class="flex items-center gap-2">
                    <CheckCircle2 class="h-3.5 w-3.5 shrink-0 text-emerald-600" />
                    <span>Klik tombol di email untuk langsung membuka Dashboard.</span>
                </div>
            </div>

            <div
                v-if="status === 'verification-link-sent'"
                role="status"
                class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-xs font-medium text-emerald-900"
            >
                Tautan verifikasi baru telah berhasil dikirim ke emailmu!
            </div>

            <Form v-bind="send.form()" v-slot="{ processing }" class="space-y-3 pt-1">
                <Button
                    type="submit"
                    class="coral-button-gradient h-11 w-full rounded-full text-sm font-bold text-white transition-all duration-200"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    <RefreshCw v-else class="mr-1.5 h-4 w-4" />
                    <span>Kirim Ulang Email Verifikasi</span>
                </Button>

                <div class="border-t border-outline-variant/40 pt-2 text-center">
                    <TextLink
                        :href="logout()"
                        as="button"
                        class="text-xs font-semibold text-on-surface-variant hover:text-primary"
                    >
                        Keluar / Gunakan akun email lain
                    </TextLink>
                </div>
            </Form>
        </div>
    </AuthLayout>
</template>
