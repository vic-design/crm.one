<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, Tag, User, UserCog, FileText } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuItem,
    SidebarMenuButton,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';
import users from '@/routes/management/users';
import roles from '@/routes/management/roles';
import tags from '@/routes/tags';
import activityLog from '@/routes/management/activity-log';

const mainNavItems: NavItem[] = [
    {
        title: 'Дашборд',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Теги',
        href: tags.index(),
        icon: Tag,
    },
    {
        title: 'Управление',
        icon: UserCog,
        children: [
            {
                title: 'Пользователи',
                href: users.index(),
            },
            {
                title: 'Роли',
                href: roles.index(),
            },
            {
                title: 'Журнал изменений',
                href: activityLog.index(),
            }
        ]
    }
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
