<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import CleaningTaskController from '@/actions/App/Http/Controllers/CleaningTaskController';
import ContactController from '@/actions/App/Http/Controllers/ContactController';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';
import ReservationController from '@/actions/App/Http/Controllers/ReservationController';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type CleaningTask } from '@/types/models';

const props = defineProps<{
    cleaningTask: CleaningTask;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Cleaning Tasks', href: CleaningTaskController.index().url },
    { title: `Task #${props.cleaningTask.id}`, href: CleaningTaskController.show(props.cleaningTask).url },
];

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('es-CL', { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' });
}

function statusColor(status: string): string {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        notified: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        in_progress: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        verified: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return colors[status] ?? '';
}

function typeLabel(type: string): string {
    const labels: Record<string, string> = {
        checkout: 'Checkout',
        deep_clean: 'Deep Clean',
        touch_up: 'Touch Up',
    };
    return labels[type] ?? type;
}

function formatFee(fee: number | null): string {
    if (fee === null) return '-';
    return `$${fee.toLocaleString('es-CL')}`;
}

function deleteTask(): void {
    if (confirm('Are you sure you want to delete this cleaning task?')) {
        router.delete(CleaningTaskController.destroy(props.cleaningTask).url);
    }
}
</script>

<template>
    <Head :title="`Cleaning Task #${cleaningTask.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Cleaning Task #{{ cleaningTask.id }}</h1>
                    <div class="mt-1 flex items-center gap-2">
                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', statusColor(cleaningTask.status)]">
                            {{ cleaningTask.status.replace('_', ' ') }}
                        </span>
                        <span class="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium">
                            {{ typeLabel(cleaningTask.cleaning_type) }}
                        </span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="CleaningTaskController.edit(cleaningTask).url"
                        class="inline-flex items-center rounded-md border bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent"
                    >
                        Edit
                    </Link>
                    <button
                        @click="deleteTask"
                        class="inline-flex items-center rounded-md border border-red-200 bg-background px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 dark:border-red-800 dark:hover:bg-red-950"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border bg-card p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-medium text-muted-foreground">Task Details</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Scheduled Date</dt>
                            <dd class="font-medium">{{ formatDate(cleaningTask.scheduled_date) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Type</dt>
                            <dd class="font-medium">{{ typeLabel(cleaningTask.cleaning_type) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Fee</dt>
                            <dd class="font-medium">{{ formatFee(cleaningTask.cleaning_fee) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border bg-card p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-medium text-muted-foreground">Assignment</h3>
                    <dl class="space-y-2 text-sm">
                        <div v-if="cleaningTask.property" class="flex justify-between">
                            <dt class="text-muted-foreground">Property</dt>
                            <dd>
                                <Link :href="PropertyController.show(cleaningTask.property).url" class="font-medium text-primary hover:underline">
                                    {{ cleaningTask.property.name }}
                                </Link>
                            </dd>
                        </div>
                        <div v-if="cleaningTask.reservation" class="flex justify-between">
                            <dt class="text-muted-foreground">Reservation</dt>
                            <dd>
                                <Link :href="ReservationController.show(cleaningTask.reservation).url" class="font-medium text-primary hover:underline">
                                    {{ cleaningTask.reservation.guest_name }}
                                </Link>
                            </dd>
                        </div>
                        <div v-if="cleaningTask.contact" class="flex justify-between">
                            <dt class="text-muted-foreground">Contact</dt>
                            <dd>
                                <Link :href="ContactController.show(cleaningTask.contact).url" class="font-medium text-primary hover:underline">
                                    {{ cleaningTask.contact.name }}
                                </Link>
                            </dd>
                        </div>
                        <div v-else-if="cleaningTask.assigned_to" class="flex justify-between">
                            <dt class="text-muted-foreground">Assigned To</dt>
                            <dd class="font-medium">{{ cleaningTask.assigned_to }}</dd>
                        </div>
                        <div v-if="!cleaningTask.contact && cleaningTask.assigned_phone" class="flex justify-between">
                            <dt class="text-muted-foreground">Phone</dt>
                            <dd class="font-medium">{{ cleaningTask.assigned_phone }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div v-if="cleaningTask.notes" class="rounded-xl border bg-card p-5 shadow-sm">
                <h3 class="mb-2 text-sm font-medium text-muted-foreground">Notes</h3>
                <p class="whitespace-pre-wrap text-sm">{{ cleaningTask.notes }}</p>
            </div>
        </div>
    </AppLayout>
</template>
