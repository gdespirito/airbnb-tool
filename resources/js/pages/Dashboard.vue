<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col items-center justify-center rounded-xl p-8">
            <span class="text-5xl">🏡</span>
            <h2 class="mt-4 text-xl font-semibold text-foreground">
                Hola, {{ user.name }}
            </h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Bienvenido a Airbnb Tool
            </p>
        </div>
    </AppLayout>
</template>
