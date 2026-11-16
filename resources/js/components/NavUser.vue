<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronsUpDown, LogOut, Settings } from '@lucide/vue';
import { computed } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

const props = defineProps<{
    user?: User;
}>();

const page = usePage();
const currentUser = computed<User>(
    () => (props.user || page.props.auth?.user || {}) as User,
);
const { isMobile } = useSidebar();

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        :tooltip="currentUser.name || 'Akun Pengguna'"
                        class="group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0 data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                    >
                        <UserInfo :user="currentUser" :show-email="true" />
                        <ChevronsUpDown
                            class="ml-auto size-4 group-data-[collapsible=icon]:hidden"
                        />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="isMobile ? 'bottom' : 'right'"
                    align="end"
                    :side-offset="4"
                >
                    <DropdownMenuLabel class="p-0 font-normal">
                        <div
                            class="flex items-center gap-2 px-1 py-1.5 text-left text-sm"
                        >
                            <UserInfo :user="currentUser" :show-email="true" />
                        </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                        <DropdownMenuItem as-child>
                            <Link
                                class="flex w-full cursor-pointer items-center gap-2"
                                :href="edit()"
                                prefetch
                            >
                                <Settings class="size-4" />
                                Pengaturan Profil
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuGroup>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem as-child>
                        <Link
                            class="flex w-full cursor-pointer items-center gap-2 text-destructive focus:text-destructive"
                            :href="logout()"
                            @click="handleLogout"
                            as="button"
                        >
                            <LogOut class="size-4" />
                            Keluar
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
