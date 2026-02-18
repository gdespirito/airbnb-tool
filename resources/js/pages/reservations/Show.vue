<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Reservation } from '@/types/models';
import ReservationController from '@/actions/App/Http/Controllers/ReservationController';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';

const props = defineProps<{
    reservation: Reservation;
    isSameDayTurnover: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reservations', href: ReservationController.index().url },
    { title: props.reservation.guest_name, href: ReservationController.show(props.reservation).url },
];

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('es-CL', { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' });
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

function deleteReservation(): void {
    if (confirm('Are you sure you want to delete this reservation?')) {
        router.delete(ReservationController.destroy(props.reservation).url);
    }
}
</script>

<template>
    <Head :title="reservation.guest_name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ reservation.guest_name }}</h1>
                    <div class="mt-1 flex items-center gap-2">
                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', statusColor(reservation.status)]">
                            {{ reservation.status.replace('_', ' ') }}
                        </span>
                        <span v-if="isSameDayTurnover" class="rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                            Same-day turnover
                        </span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="ReservationController.edit(reservation).url"
                        class="inline-flex items-center rounded-md border bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent"
                    >
                        Edit
                    </Link>
                    <button
                        @click="deleteReservation"
                        class="inline-flex items-center rounded-md border border-red-200 bg-background px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 dark:border-red-800 dark:hover:bg-red-950"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border bg-card p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-medium text-muted-foreground">Stay Details</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Check-in</dt>
                            <dd class="font-medium">{{ formatDate(reservation.check_in) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Check-out</dt>
                            <dd class="font-medium">{{ formatDate(reservation.check_out) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Guests</dt>
                            <dd class="font-medium">{{ reservation.number_of_guests }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Source</dt>
                            <dd class="font-medium capitalize">{{ reservation.source }}</dd>
                        </div>
                        <div v-if="reservation.airbnb_reservation_id" class="flex justify-between">
                            <dt class="text-muted-foreground">Airbnb ID</dt>
                            <dd class="font-medium">{{ reservation.airbnb_reservation_id }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border bg-card p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-medium text-muted-foreground">Guest & Property</h3>
                    <dl class="space-y-2 text-sm">
                        <div v-if="reservation.property" class="flex justify-between">
                            <dt class="text-muted-foreground">Property</dt>
                            <dd>
                                <Link :href="PropertyController.show(reservation.property).url" class="font-medium text-primary hover:underline">
                                    {{ reservation.property.name }}
                                </Link>
                            </dd>
                        </div>
                        <div v-if="reservation.guest_phone" class="flex justify-between">
                            <dt class="text-muted-foreground">Phone</dt>
                            <dd class="font-medium">{{ reservation.guest_phone }}</dd>
                        </div>
                        <div v-if="reservation.guest_email" class="flex justify-between">
                            <dt class="text-muted-foreground">Email</dt>
                            <dd class="font-medium">{{ reservation.guest_email }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div v-if="reservation.notes" class="rounded-xl border bg-card p-5 shadow-sm">
                <h3 class="mb-2 text-sm font-medium text-muted-foreground">Notes</h3>
                <p class="whitespace-pre-wrap text-sm">{{ reservation.notes }}</p>
            </div>
        </div>
    </AppLayout>
</template>
