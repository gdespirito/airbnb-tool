<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Property, type Reservation } from '@/types/models';
import ReservationController from '@/actions/App/Http/Controllers/ReservationController';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';

defineProps<{
    reservations: (Reservation & { is_same_day_turnover: boolean })[];
    properties: Pick<Property, 'id' | 'name' | 'slug'>[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reservations', href: ReservationController.index().url },
];

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('es-CL', { day: 'numeric', month: 'short', year: 'numeric' });
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

function statusLabel(status: string): string {
    return status.replace('_', ' ');
}
</script>

<template>
    <Head title="Reservations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Reservations</h1>
                <Link
                    :href="ReservationController.create().url"
                    class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
                >
                    Add Reservation
                </Link>
            </div>

            <div v-if="reservations.length === 0" class="rounded-xl border border-dashed p-12 text-center text-muted-foreground">
                No upcoming reservations.
            </div>

            <div v-else class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Guest</th>
                            <th class="px-4 py-3 text-left font-medium">Property</th>
                            <th class="px-4 py-3 text-left font-medium">Check-in</th>
                            <th class="px-4 py-3 text-left font-medium">Check-out</th>
                            <th class="px-4 py-3 text-left font-medium">Guests</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="reservation in reservations" :key="reservation.id" class="hover:bg-muted/30">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ reservation.guest_name }}</div>
                                <div v-if="reservation.is_same_day_turnover" class="mt-0.5 text-xs font-medium text-orange-600 dark:text-orange-400">
                                    Same-day turnover
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    v-if="reservation.property"
                                    :href="PropertyController.show(reservation.property).url"
                                    class="text-primary hover:underline"
                                >
                                    {{ reservation.property.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3">{{ formatDate(reservation.check_in) }}</td>
                            <td class="px-4 py-3">{{ formatDate(reservation.check_out) }}</td>
                            <td class="px-4 py-3">{{ reservation.number_of_guests }}</td>
                            <td class="px-4 py-3">
                                <span :class="['rounded-full px-2 py-0.5 text-xs font-medium capitalize', statusColor(reservation.status)]">
                                    {{ statusLabel(reservation.status) }}
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
    </AppLayout>
</template>
