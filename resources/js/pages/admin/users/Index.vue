<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    CheckCircle2,
    Clock,
    MailCheck,
    MailX,
    Power,
    Search,
    Shield,
    Trash2,
    UserCheck,
    Users,
    UserX,
    X,
} from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
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
import { useInitials } from '@/composables/useInitials';
import type { User as AuthUser } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Manajemen User', href: '/users' },
        ],
    },
});

type UserItem = {
    id: string;
    name: string;
    email: string;
    is_admin: boolean;
    is_active: boolean;
    email_verified_at: string | null;
    created_at: string;
    events_count: number;
};

type PaginationProps = {
    data: UserItem[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    users: PaginationProps;
    filters: {
        filter: string;
        search: string;
    };
    stats: {
        total: number;
        active: number;
        inactive: number;
        unverified: number;
    };
}>();

const { getInitials } = useInitials();
const page = usePage();
const currentAuthUser = computed(
    () => page.props.auth?.user as (AuthUser & { is_admin?: boolean }) | null,
);

const searchQuery = ref(props.filters.search || '');
const activeFilter = ref(props.filters.filter || 'all');

const filterOptions = [
    { value: 'all', label: 'Semua', count: props.stats.total },
    { value: 'active', label: 'Aktif', count: props.stats.active },
    { value: 'inactive', label: 'Nonaktif', count: props.stats.inactive },
    { value: 'verified', label: 'Email Terverifikasi' },
    {
        value: 'unverified',
        label: 'Belum Verifikasi',
        count: props.stats.unverified,
    },
    { value: 'client', label: 'Klien' },
    { value: 'admin', label: 'Admin' },
];

function applyFilters(pageNumber?: number) {
    const query: Record<string, string | number> = {};
    const trimmed = searchQuery.value.trim();
    if (trimmed) {
        query.search = trimmed;
    }
    if (activeFilter.value && activeFilter.value !== 'all') {
        query.filter = activeFilter.value;
    }
    if (pageNumber && pageNumber > 1) {
        query.page = pageNumber;
    }

    router.get('/users', query, {
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

onMounted(() => {
    if (window.location.search) {
        window.history.replaceState(
            window.history.state,
            '',
            window.location.pathname,
        );
    }
});

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 350);

watch(searchQuery, () => {
    debouncedSearch();
});

function selectFilter(filter: string) {
    activeFilter.value = filter;
    applyFilters();
}

function clearSearch() {
    searchQuery.value = '';
    applyFilters();
}

function goToPage(p: number) {
    if (p === props.users.current_page) return;
    applyFilters(p);
}

function toggleStatus(user: UserItem) {
    router.patch(
        `/users/${user.id}/status`,
        {},
        {
            preserveScroll: true,
        },
    );
}

function toggleVerification(user: UserItem) {
    router.patch(
        `/users/${user.id}/verify`,
        {},
        {
            preserveScroll: true,
        },
    );
}

function deleteUser(id: string) {
    router.delete(`/users/${id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Manajemen User · Admin" />

    <div class="flex flex-col gap-6 p-4 sm:p-6">
        <!-- Page Header -->
        <header
            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Manajemen User
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Kelola akun pengguna, pantau verifikasi email, serta
                    aktifkan atau nonaktifkan akses masuk klien.
                </p>
            </div>
        </header>

        <!-- Stats Overview Cards -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            <div
                class="rounded-xl border border-border/80 bg-card p-4 shadow-xs"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground"
                        >Total Pengguna</span
                    >
                    <div class="rounded-lg bg-primary/10 p-2 text-primary">
                        <Users class="size-4" />
                    </div>
                </div>
                <p
                    class="mt-2 text-2xl font-bold tracking-tight text-foreground"
                >
                    {{ stats.total }}
                </p>
            </div>

            <div
                class="rounded-xl border border-border/80 bg-card p-4 shadow-xs"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground"
                        >Akun Aktif</span
                    >
                    <div
                        class="rounded-lg bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400"
                    >
                        <UserCheck class="size-4" />
                    </div>
                </div>
                <p
                    class="mt-2 text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400"
                >
                    {{ stats.active }}
                </p>
            </div>

            <div
                class="rounded-xl border border-border/80 bg-card p-4 shadow-xs"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground"
                        >Akun Nonaktif</span
                    >
                    <div
                        class="rounded-lg bg-destructive/10 p-2 text-destructive"
                    >
                        <UserX class="size-4" />
                    </div>
                </div>
                <p
                    class="mt-2 text-2xl font-bold tracking-tight text-destructive"
                >
                    {{ stats.inactive }}
                </p>
            </div>

            <div
                class="rounded-xl border border-border/80 bg-card p-4 shadow-xs"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground"
                        >Belum Verif Email</span
                    >
                    <div
                        class="rounded-lg bg-amber-500/10 p-2 text-amber-600 dark:text-amber-400"
                    >
                        <Clock class="size-4" />
                    </div>
                </div>
                <p
                    class="mt-2 text-2xl font-bold tracking-tight text-amber-600 dark:text-amber-400"
                >
                    {{ stats.unverified }}
                </p>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <!-- Filter Pills -->
            <div class="flex flex-wrap items-center gap-1.5">
                <Button
                    v-for="opt in filterOptions"
                    :key="opt.value"
                    size="sm"
                    :variant="
                        activeFilter === opt.value ? 'default' : 'outline'
                    "
                    class="h-8 rounded-full text-xs"
                    @click="selectFilter(opt.value)"
                >
                    {{ opt.label }}
                    <span
                        v-if="opt.count !== undefined"
                        class="py-0.2 ml-1 rounded-full px-1.5 text-[10px] font-semibold"
                        :class="
                            activeFilter === opt.value
                                ? 'bg-primary-foreground/20 text-primary-foreground'
                                : 'bg-muted text-muted-foreground'
                        "
                    >
                        {{ opt.count }}
                    </span>
                </Button>
            </div>

            <!-- Search Field -->
            <div class="relative w-full sm:w-72">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari nama atau email..."
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

        <!-- Users Table Card -->
        <div
            class="overflow-hidden rounded-xl border border-border/80 bg-card shadow-xs"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b bg-muted/40 text-xs text-muted-foreground"
                    >
                        <tr>
                            <th
                                class="w-12 px-3 py-3 text-center font-semibold"
                            >
                                No
                            </th>
                            <th class="px-4 py-3 font-semibold">Pengguna</th>
                            <th class="px-4 py-3 font-semibold">Status Akun</th>
                            <th class="px-4 py-3 font-semibold">
                                Verifikasi Email
                            </th>
                            <th class="px-4 py-3 font-semibold">Acara</th>
                            <th class="px-4 py-3 font-semibold">Terdaftar</th>
                            <th class="px-4 py-3 text-right font-semibold">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <tr
                            v-for="(u, index) in users.data"
                            :key="u.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <!-- Row Number -->
                            <td
                                class="w-12 px-3 py-3.5 text-center text-xs font-semibold text-muted-foreground"
                            >
                                {{ (users.from ?? 1) + index }}
                            </td>

                            <!-- User Info -->
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                        :class="
                                            u.is_admin
                                                ? 'bg-primary/10 text-primary'
                                                : 'bg-muted text-foreground'
                                        "
                                    >
                                        {{ getInitials(u.name) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="font-medium text-foreground"
                                                >{{ u.name }}</span
                                            >
                                            <Badge
                                                v-if="u.is_admin"
                                                variant="secondary"
                                                class="h-4.5 px-1.5 text-[10px] font-semibold text-primary"
                                            >
                                                <Shield
                                                    class="mr-0.5 size-2.5"
                                                />
                                                Admin
                                            </Badge>
                                            <span
                                                v-if="
                                                    currentAuthUser?.id === u.id
                                                "
                                                class="text-[10px] font-medium text-muted-foreground"
                                            >
                                                (Anda)
                                            </span>
                                        </div>
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >{{ u.email }}</span
                                        >
                                    </div>
                                </div>
                            </td>

                            <!-- Account Status -->
                            <td class="px-4 py-3.5">
                                <span
                                    v-if="u.is_active"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-400"
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-emerald-500"
                                    ></span>
                                    Aktif
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 rounded-full border border-destructive/20 bg-destructive/10 px-2.5 py-0.5 text-xs font-medium text-destructive"
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-destructive"
                                    ></span>
                                    Nonaktif
                                </span>
                            </td>

                            <!-- Email Verification -->
                            <td class="px-4 py-3.5">
                                <span
                                    v-if="u.email_verified_at"
                                    class="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-400"
                                    title="Email telah terverifikasi"
                                >
                                    <CheckCircle2
                                        class="size-3 text-emerald-600 dark:text-emerald-400"
                                    />
                                    Terverifikasi
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400"
                                    title="Belum memverifikasi tautan di email"
                                >
                                    <Clock
                                        class="size-3 text-amber-600 dark:text-amber-400"
                                    />
                                    Belum Verifikasi
                                </span>
                            </td>

                            <!-- Events Count -->
                            <td class="px-4 py-3.5">
                                <div
                                    class="flex items-center gap-1.5 text-xs text-muted-foreground"
                                >
                                    <CalendarDays
                                        class="size-3.5 text-muted-foreground"
                                    />
                                    <span class="font-medium text-foreground">{{
                                        u.events_count
                                    }}</span>
                                    <span>acara</span>
                                </div>
                            </td>

                            <!-- Registration Date -->
                            <td
                                class="px-4 py-3.5 text-xs whitespace-nowrap text-muted-foreground"
                            >
                                {{ u.created_at }}
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3.5 text-right">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <!-- Toggle Active / Inactive Status -->
                                    <Button
                                        size="sm"
                                        :variant="
                                            u.is_active ? 'outline' : 'default'
                                        "
                                        class="h-8 gap-1.5 text-xs"
                                        :disabled="currentAuthUser?.id === u.id"
                                        :title="
                                            currentAuthUser?.id === u.id
                                                ? 'Tidak dapat mengubah status akun sendiri'
                                                : u.is_active
                                                  ? 'Klik untuk nonaktifkan user'
                                                  : 'Klik untuk aktifkan user'
                                        "
                                        @click="toggleStatus(u)"
                                    >
                                        <Power
                                            class="size-3.5"
                                            :class="
                                                u.is_active
                                                    ? 'text-destructive'
                                                    : 'text-emerald-500'
                                            "
                                        />
                                        <span>{{
                                            u.is_active
                                                ? 'Nonaktifkan'
                                                : 'Aktifkan'
                                        }}</span>
                                    </Button>

                                    <!-- Quick Manual Email Verification Toggle -->
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        class="h-8 px-2 text-xs"
                                        :disabled="currentAuthUser?.id === u.id"
                                        :title="
                                            currentAuthUser?.id === u.id
                                                ? 'Tidak dapat mengubah verifikasi akun sendiri'
                                                : u.email_verified_at
                                                  ? 'Batalkan verifikasi email'
                                                  : 'Verifikasi email secara manual'
                                        "
                                        @click="toggleVerification(u)"
                                    >
                                        <MailCheck
                                            v-if="!u.email_verified_at"
                                            class="size-3.5 text-emerald-600"
                                        />
                                        <MailX
                                            v-else
                                            class="size-3.5 text-amber-600"
                                        />
                                        <span class="sr-only">
                                            {{
                                                u.email_verified_at
                                                    ? 'Batalkan verifikasi email'
                                                    : 'Verifikasi email'
                                            }}
                                        </span>
                                    </Button>

                                    <!-- Delete User -->
                                    <ConfirmDeleteDialog
                                        :title="`Hapus Pengguna ${u.name}?`"
                                        :description="`Tindakan ini akan menghapus akun ${u.email}. Acara yang terkait dengan pengguna ini akan tetap tersimpan di database.`"
                                        confirm-label="Hapus Pengguna"
                                        @confirm="deleteUser(u.id)"
                                    >
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            class="h-8 px-2 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                            :disabled="
                                                currentAuthUser?.id === u.id
                                            "
                                            :title="
                                                currentAuthUser?.id === u.id
                                                    ? 'Tidak dapat menghapus akun sendiri'
                                                    : 'Hapus pengguna'
                                            "
                                        >
                                            <Trash2 class="size-3.5" />
                                            <span class="sr-only">Hapus</span>
                                        </Button>
                                    </ConfirmDeleteDialog>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="users.data.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <div
                                    class="mx-auto flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground"
                                >
                                    <Users class="size-6" />
                                </div>
                                <p class="mt-3 font-medium text-foreground">
                                    Tidak ada pengguna yang cocok
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Coba ubah kata kunci pencarian atau ganti
                                    filter yang dipilih.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div
                v-if="users.total > 0"
                class="flex flex-col gap-3 border-t px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-xs text-muted-foreground">
                    Menampilkan
                    <span class="font-medium text-foreground">{{
                        users.from ?? 0
                    }}</span>
                    sampai
                    <span class="font-medium text-foreground">{{
                        users.to ?? 0
                    }}</span>
                    dari
                    <span class="font-medium text-foreground">{{
                        users.total
                    }}</span>
                    pengguna
                </p>
                <Pagination
                    v-if="users.last_page > 1"
                    :total="users.total"
                    :items-per-page="15"
                    :sibling-count="1"
                    :default-page="users.current_page"
                    :page="users.current_page"
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
                                        item.value === users.current_page
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
