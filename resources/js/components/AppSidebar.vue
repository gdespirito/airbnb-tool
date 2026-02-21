<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CalendarDays, Home, LayoutGrid, Sparkles, Users } from 'lucide-vue-next';
import CleaningTaskController from '@/actions/App/Http/Controllers/CleaningTaskController';
import ContactController from '@/actions/App/Http/Controllers/ContactController';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';
import ReservationController from '@/actions/App/Http/Controllers/ReservationController';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Properties',
        href: PropertyController.index().url,
        icon: Home,
    },
    {
        title: 'Reservations',
        href: ReservationController.index().url,
        icon: CalendarDays,
    },
    {
        title: 'Cleaning Tasks',
        href: CleaningTaskController.index().url,
        icon: Sparkles,
    },
    {
        title: 'Contacts',
        href: ContactController.index().url,
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Hostex',
        href: 'https://hostex.io',
        icon: Home,
    },
    {
        title: 'Airbnb',
        href: 'https://airbnb.cl',
        icon: CalendarDays,
    },
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
