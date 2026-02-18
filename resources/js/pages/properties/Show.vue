<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Property, type Reservation } from '@/types/models';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';
import ReservationController from '@/actions/App/Http/Controllers/ReservationController';

const props = defineProps<{
    property: Property;
    todayCheckouts: Reservation[];
    todayCheckins: Reservation[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Properties', href: PropertyController.index().url },
    { title: props.property.name, href: PropertyController.show(props.property).url },
];

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('es-CL', { day: 'numeric', month: 'short' });
}

function statusColor(status: string): string {
    const colors: Record<string, string> = {
        confirmed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        checked_in: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        checked_out: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return colors[status] ?? '';
}
</script>

<template>
    <Head :title="property.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ property.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ property.location }}</p>
                </div>
                <Link
                    :href="PropertyController.edit(property).url"
                    class="inline-flex items-center rounded-md border bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent"
                >
                    Edit
                </Link>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border bg-card p-4 shadow-sm">
                    <p class="text-sm text-muted-foreground">Check-in</p>
                    <p class="text-lg font-semibold">{{ property.checkin_time }}</p>
                </div>
                <div class="rounded-xl border bg-card p-4 shadow-sm">
                    <p class="text-sm text-muted-foreground">Check-out</p>
                    <p class="text-lg font-semibold">{{ property.checkout_time }}</p>
                </div>
                <div class="rounded-xl border bg-card p-4 shadow-sm">
                    <p class="text-sm text-muted-foreground">Cleaning</p>
                    <p class="text-lg font-semibold">{{ property.cleaning_contact_name || 'Not assigned' }}</p>
                    <p v-if="property.cleaning_contact_phone" class="text-sm text-muted-foreground">{{ property.cleaning_contact_phone }}</p>
                </div>
            </div>

            <div v-if="todayCheckins.length || todayCheckouts.length" class="grid gap-4 md:grid-cols-2">
                <div v-if="todayCheckins.length" class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950">
                    <h3 class="mb-2 font-semibold text-green-800 dark:text-green-200">Check-ins Today</h3>
                    <ul class="space-y-1">
                        <li v-for="r in todayCheckins" :key="r.id" class="text-sm">
                            {{ r.guest_name }} ({{ r.number_of_guests }} guests)
                        </li>
                    </ul>
                </div>
                <div v-if="todayCheckouts.length" class="rounded-xl border border-orange-200 bg-orange-50 p-4 dark:border-orange-800 dark:bg-orange-950">
                    <h3 class="mb-2 font-semibold text-orange-800 dark:text-orange-200">Check-outs Today</h3>
                    <ul class="space-y-1">
                        <li v-for="r in todayCheckouts" :key="r.id" class="text-sm">
                            {{ r.guest_name }}
                        </li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Upcoming Reservations</h2>
                    <Link
                        :href="ReservationController.create().url"
                        class="inline-flex items-center rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
                    >
                        Add Reservation
                    </Link>
                </div>

                <div v-if="!property.reservations?.length" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                    No upcoming reservations.
                </div>

                <div v-else class="overflow-hidden rounded-xl border">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Guest</th>
                                <th class="px-4 py-3 text-left font-medium">Check-in</th>
                                <th class="px-4 py-3 text-left font-medium">Check-out</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-right font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="reservation in property.reservations" :key="reservation.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3 font-medium">{{ reservation.guest_name }}</td>
                                <td class="px-4 py-3">{{ formatDate(reservation.check_in) }}</td>
                                <td class="px-4 py-3">{{ formatDate(reservation.check_out) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusColor(reservation.status)]">
                                        {{ reservation.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="ReservationController.show(reservation).url" class="text-primary hover:underline">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
