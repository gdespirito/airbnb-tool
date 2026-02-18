<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Property } from '@/types/models';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';

defineProps<{
    properties: Property[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Properties',
        href: PropertyController.index().url,
    },
];
</script>

<template>
    <Head title="Properties" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Properties</h1>
                <Link
                    :href="PropertyController.create()"
                    class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
                >
                    Add Property
                </Link>
            </div>

            <div v-if="properties.length === 0" class="rounded-xl border border-dashed p-12 text-center text-muted-foreground">
                No properties yet. Add your first property to get started.
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="property in properties"
                    :key="property.id"
                    class="flex flex-col gap-4 rounded-xl border bg-card p-6 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h2 class="text-xl font-bold">{{ property.name }}</h2>
                            <p class="text-sm text-muted-foreground">{{ property.location }}</p>
                        </div>
                        <span
                            v-if="property.upcoming_reservations_count !== undefined"
                            class="shrink-0 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                        >
                            {{ property.upcoming_reservations_count }} upcoming
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-muted-foreground">Check-in:</span>
                            <span class="ml-1 font-medium">{{ property.checkin_time }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Check-out:</span>
                            <span class="ml-1 font-medium">{{ property.checkout_time }}</span>
                        </div>
                        <div v-if="property.cleaning_contact_name" class="col-span-2">
                            <span class="text-muted-foreground">Cleaning:</span>
                            <span class="ml-1 font-medium">{{ property.cleaning_contact_name }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <Link
                            :href="PropertyController.show(property)"
                            class="inline-flex items-center rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
                        >
                            View
                        </Link>
                        <Link
                            :href="PropertyController.edit(property)"
                            class="inline-flex items-center rounded-md border bg-background px-3 py-1.5 text-sm font-medium shadow-sm hover:bg-accent"
                        >
                            Edit
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
