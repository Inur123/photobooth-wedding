<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CalendarDays,
    Eye,
    Pencil,
    Plus,
    Search,
    Trash2,
    X,
} from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Pagination,
    PaginationEllipsis,
    PaginationFirst,
    PaginationLast,
    PaginationList,
    PaginationListItem,
    PaginationNext,
    PaginationPrev,
} from '@/components/ui/pagination';
import { getEcho } from '@/lib/echo';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Daftar Acara', href: '/events' },
        ],
    },
});

const props = withDefaults(
    defineProps<{
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
            from: number | null;
            to: number | null;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
        filters?: {
            search?: string;
        };
    }>(),
    {
        filters: () => ({ search: '' }),
    },
);

const searchQuery = ref(props.filters?.search || '');

function applyFilters(pageNumber?: number) {
    const query: Record<string, string | number> = {};
    const trimmed = searchQuery.value.trim();
    if (trimmed) {
        query.search = trimmed;
    }
    if (pageNumber && pageNumber > 1) {
        query.page = pageNumber;
    }

    router.get('/events', query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            window.history.replaceState(
                window.history.state,
                '',
                window.location.pathname,
            );
        },
    });
}

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 350);

watch(searchQuery, () => {
    debouncedSearch();
});

function clearSearch() {
    searchQuery.value = '';
    applyFilters();
}

function goToPage(p: number) {
    if (p === props.events.current_page) return;
    applyFilters(p);
}

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
    if (window.location.search) {
        window.history.replaceState(
            window.history.state,
            '',
            window.location.pathname,
        );
    }

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

        <!-- Search Bar -->
        <div class="flex items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari nama acara, slug, klien, lokasi..."
                    class="h-9 pr-8 pl-9 text-xs"
                />
                <button
                    v-if="searchQuery"
                    type="button"
                    class="absolute top-1/2 right-2.5 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                    @click="clearSearch"
                >
                    <X class="size-3.5" />
                    <span class="sr-only">Hapus pencarian</span>
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border bg-card shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/40 text-xs">
                        <tr>
                            <th
                                class="w-12 px-3 py-3 text-center font-semibold text-muted-foreground"
                            >
                                No
                            </th>
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
                            v-for="(ev, index) in events.data"
                            :key="ev.id"
                            class="transition-colors hover:bg-muted/20"
                        >
                            <!-- Row Number -->
                            <td
                                class="w-12 px-3 py-3.5 text-center text-xs font-semibold text-muted-foreground"
                            >
                                {{ (events.from ?? 1) + index }}
                            </td>

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
                                            'outline'
                                        "
                                        class="capitalize"
                                    >
                                        {{
                                            statusLabel[ev.status] ?? ev.status
                                        }}
                                    </Badge>
                                    <span
                                        v-if="ev.is_manual_status"
                                        class="text-[10px] font-semibold text-amber-700 dark:text-amber-400"
                                    >
                                        Manual Admin
                                    </span>
                                    <span
                                        v-else
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        Otomatis
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                        title="Buka Halaman Acara"
                                    >
                                        <a
                                            :href="`/e/${ev.slug}`"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <Eye
                                                class="size-4"
                                                aria-hidden="true"
                                            />
                                            <span class="sr-only"
                                                >Lihat acara</span
                                            >
                                        </a>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                        title="Edit Acara"
                                    >
                                        <Link :href="`/events/${ev.id}/edit`">
                                            <Pencil
                                                class="size-4"
                                                aria-hidden="true"
                                            />
                                            <span class="sr-only"
                                                >Edit acara</span
                                            >
                                        </Link>
                                    </Button>
                                    <ConfirmDeleteDialog
                                        :title="`Hapus acara ${ev.party_one_name}?`"
                                        description="Tindakan ini akan menghapus data acara secara permanen beserta seluruh foto dan pesan yang terkait."
                                        confirm-label="Hapus Acara"
                                        @confirm="deleteEvent(ev.id)"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                            title="Hapus Acara"
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
                        <!-- Empty State -->
                        <tr v-if="events.data.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <div
                                    class="mx-auto flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground"
                                >
                                    <CalendarDays class="size-6" />
                                </div>
                                <p class="mt-3 font-medium text-foreground">
                                    {{
                                        searchQuery
                                            ? 'Tidak ada acara yang cocok'
                                            : 'Belum ada acara terdaftar'
                                    }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{
                                        searchQuery
                                            ? 'Coba ubah kata kunci pencarian.'
                                            : 'Mulai buat acara baru untuk klien.'
                                    }}
                                </p>
                                <div v-if="!searchQuery" class="mt-4">
                                    <Button as-child size="sm">
                                        <Link href="/events/create">
                                            <Plus class="mr-1.5 size-4" />
                                            Buat Acara Baru
                                        </Link>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div
                v-if="events.total > 0"
                class="flex flex-col gap-3 border-t px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-xs text-muted-foreground">
                    Menampilkan
                    <span class="font-medium text-foreground">{{
                        events.from ?? 0
                    }}</span>
                    sampai
                    <span class="font-medium text-foreground">{{
                        events.to ?? 0
                    }}</span>
                    dari
                    <span class="font-medium text-foreground">{{
                        events.total
                    }}</span>
                    acara
                </p>
                <Pagination
                    v-if="events.last_page > 1"
                    :total="events.total"
                    :items-per-page="15"
                    :sibling-count="1"
                    :default-page="events.current_page"
                    :page="events.current_page"
                    show-edges
                    @update:page="goToPage"
                >
                    <PaginationList
                        v-slot="{ items }"
                        class="flex items-center gap-1"
                    >
                        <PaginationFirst />
                        <PaginationPrev />
                        <template v-for="(item, index) in items">
                            <PaginationListItem
                                v-if="item.type === 'page'"
                                :key="index"
                                :value="item.value"
                                as-child
                            >
                                <Button
                                    size="sm"
                                    class="size-8 p-0 text-xs"
                                    :variant="
                                        item.value === events.current_page
                                            ? 'default'
                                            : 'outline'
                                    "
                                >
                                    {{ item.value }}
                                </Button>
                            </PaginationListItem>
                            <PaginationEllipsis
                                v-else
                                :key="item.type"
                                :index="index"
                            />
                        </template>
                        <PaginationNext />
                        <PaginationLast />
                    </PaginationList>
                </Pagination>
            </div>
        </div>
    </div>
</template>
