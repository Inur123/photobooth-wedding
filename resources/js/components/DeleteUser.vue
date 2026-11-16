<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Label } from '@/components/ui/label';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Hapus akun"
            description="Hapus akun beserta seluruh data yang terkait"
        />
        <div
            class="space-y-4 rounded-xl border border-destructive/25 bg-destructive/5 p-4"
        >
            <div class="relative space-y-0.5 text-destructive">
                <p class="font-medium">Peringatan</p>
                <p class="text-sm">
                    Tindakan ini permanen dan tidak dapat dibatalkan.
                </p>
            </div>
            <AlertDialog>
                <AlertDialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button"
                        >Hapus akun</Button
                    >
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <AlertDialogHeader class="space-y-3">
                            <AlertDialogTitle
                                >Yakin ingin menghapus akun?</AlertDialogTitle
                            >
                            <AlertDialogDescription>
                                Seluruh data akun akan dihapus permanen.
                                Masukkan kata sandi untuk mengonfirmasi.
                            </AlertDialogDescription>
                        </AlertDialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only"
                                >Kata sandi</Label
                            >
                            <PasswordInput
                                id="password"
                                name="password"
                                ref="passwordInput"
                                placeholder="Kata sandi"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <AlertDialogFooter class="gap-2">
                            <AlertDialogCancel as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    Batal
                                </Button>
                            </AlertDialogCancel>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                Hapus akun
                            </Button>
                        </AlertDialogFooter>
                    </Form>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </div>
</template>
