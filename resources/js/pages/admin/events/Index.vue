<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from '@lucide/vue';
import { onMounted, onUnmounted } from 'vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { getEcho } from '@/lib/echo';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Daftar Acara', href: '/events' },
        ],
    },
});

const props = defineProps<{
    events: {
        data: Array<{
            id: string;
            slug: string;
            party_one_name: string;
            party_two_name: string | null;
            status: string;
            is_manual_status?: boolean;
            category: string;
            event_date: string | null;
            venue: string | null;
            contributions_count: number;
            package: { id: string; name: string } | null;
            theme: { id: string; name: string } | null;
            user?: { id: string; name: string; email: string } | null;
        }>;
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

const statusVariant: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    draft: 'secondary',
    active: 'default',
    closed: 'outline',
    expired: 'destructive',
};

const statusLabel: Record<string, string> = {
    draft: 'Draft',
    active: 'Aktif',
    closed: 'Selesai',
    expired: 'Kadaluarsa',
};

function deleteEvent(id: string) {
    router.delete(`/events/${id}`, { preserveScroll: true });
}

onMounted(() => {
    const echo = getEcho();
    if (!echo) return;

    echo.channel('admin.events').listen(
        '.EventStatusUpdated',
        (e: {
            event_id: string;
            status: string;
            is_manual_status: boolean;
        }) => {
            const ev = props.events.data.find((item) => item.id === e.event_id);
            if (ev) {
                ev.status = e.status;
                ev.is_manual_status = e.is_manual_status;
            }
        },
    );
});

onUnmounted(() => {
    const echo = getEcho();
    if (echo) {
        echo.leaveChannel('admin.events');
    }
});
</script>

<template>
    <Head title="Daftar Acara" />

    <div class="flex flex-col gap-5 p-4 sm:p-6">
        <header
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Daftar Acara
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    {{ events.total }} acara terdaftar di sistem.
                </p>
            </div>
            <Button class="w-full sm:w-auto" as-child>
                <Link href="/events/create">
                    <Plus class="mr-2 size-4" aria-hidden="true" />
                    Buat Acara
                </Link>
            </Button>
        </header>

        <div class="overflow-hidden rounded-xl border bg-card shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/40">
                        <tr>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Acara
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Paket
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Tanggal
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Kiriman
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Status
                            </th>
                            <th
                                class="px-4 py-3 text-right font-medium text-muted-foreground"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="ev in events.data"
                            :key="ev.id"
                            class="transition-colors hover:bg-muted/20"
                        >
                            <td class="px-4 py-3">
                                <p
                                    class="notranslate font-medium text-foreground"
                                    translate="no"
                                >
                                    {{ ev.party_one_name }}
                                    <span v-if="ev.party_two_name">
                                        &amp; {{ ev.party_two_name }}</span
                                    >
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    /e/{{ ev.slug }}
                                </p>
                                <p
                                    v-if="ev.user"
                                    class="mt-0.5 text-xs text-brand-muted"
                                >
                                    Klien: {{ ev.user.name }} ({{
                                        ev.user.email
                                    }})
                                </p>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ ev.package?.name ?? 'Belum dipilih' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{
                                    ev.event_date
                                        ? new Date(
                                              ev.event_date,
                                          ).toLocaleDateString('id-ID')
                                        : 'Belum diatur'
                                }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-foreground">{{
                                    ev.contributions_count
                                }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col items-start gap-1">
                                    <Badge
                                        :variant="
                                            statusVariant[ev.status] ??
                                            'secondary'
                                        "
                                    >
                                        {{
                                            statusLabel[ev.status] ?? ev.status
                                        }}
                                    </Badge>
                                    <span
                                        v-if="ev.is_manual_status"
                                        class="text-[11px] font-medium text-amber-700"
                                    >
                                        Manual Admin
                                    </span>
                                    <span
                                        v-else
                                        class="text-[11px] text-muted-foreground"
                                    >
                                        Otomatis
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                    >
                                        <a
                                            :href="`/e/${ev.slug}`"
                                            target="_blank"
                                            :aria-label="`Lihat halaman tamu ${ev.party_one_name}`"
                                        >
                                            <Eye
                                                class="size-4"
                                                aria-hidden="true"
                                            />
                                        </a>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                    >
                                        <Link
                                            :href="`/events/${ev.id}/edit`"
                                            :aria-label="`Ubah acara ${ev.party_one_name}`"
                                        >
                                            <Pencil
                                                class="size-4"
                                                aria-hidden="true"
                                            />
                                        </Link>
                                    </Button>
                                    <ConfirmDeleteDialog
                                        :title="`Hapus acara ${ev.party_one_name}?`"
                                        description="Semua kiriman tamu pada acara ini ikut terhapus dan tindakan ini tidak dapat dibatalkan."
                                        @confirm="deleteEvent(ev.id)"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="text-destructive hover:text-destructive"
                                            :aria-label="`Hapus acara ${ev.party_one_name}`"
                                        >
                                            <Trash2
                                                class="size-4"
                                                aria-hidden="true"
                                            />
                                        </Button>
                                    </ConfirmDeleteDialog>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="events.data.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                Belum ada acara terdaftar.
                                <Link
                                    href="/events/create"
                                    class="font-medium text-primary hover:underline"
                                >
                                    Buat acara pertama
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="events.last_page > 1"
                class="flex items-center justify-between border-t px-4 py-3"
            >
                <p class="text-sm text-muted-foreground">
                    Halaman {{ events.current_page }} dari
                    {{ events.last_page }}
                </p>
                <div class="flex gap-1">
                    <Button
                        v-for="link in events.links"
                        :key="link.label"
                        variant="outline"
                        size="sm"
                        :disabled="!link.url"
                        :class="{
                            'bg-primary text-primary-foreground': link.active,
                        }"
                        @click="link.url && router.get(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
