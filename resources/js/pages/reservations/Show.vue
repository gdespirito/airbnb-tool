<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import CleaningTaskController from '@/actions/App/Http/Controllers/CleaningTaskController';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';
import ReservationController from '@/actions/App/Http/Controllers/ReservationController';
import { respond } from '@/actions/App/Http/Controllers/ReservationNoteController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Reservation, type ReservationNote } from '@/types/models';

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

function cleaningStatusLabel(status: string): string {
    const labels: Record<string, string> = {
        pending: 'Pending',
        notified: 'Notified - waiting confirmation',
        in_progress: 'In progress - cleaning now',
        completed: 'Completed',
        verified: 'Verified',
    };
    return labels[status] ?? status;
}

function cleaningStatusDot(status: string): string {
    const colors: Record<string, string> = {
        pending: 'bg-gray-400',
        notified: 'bg-blue-500',
        in_progress: 'bg-purple-500 animate-pulse',
        completed: 'bg-green-500',
        verified: 'bg-green-700',
    };
    return colors[status] ?? 'bg-gray-400';
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

function formatRelativeDate(date: string): string {
    const now = new Date();
    const d = new Date(date);
    const diffMs = now.getTime() - d.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMins < 1) {
        return 'just now';
    }
    if (diffMins < 60) {
        return `${diffMins}m ago`;
    }
    if (diffHours < 24) {
        return `${diffHours}h ago`;
    }
    if (diffDays < 7) {
        return `${diffDays}d ago`;
    }
    return d.toLocaleDateString('es-CL', { day: 'numeric', month: 'short', year: 'numeric' });
}

const respondForms = new Map<number, ReturnType<typeof useForm<{ content: string }>>>();

function getRespondForm(note: ReservationNote): ReturnType<typeof useForm<{ content: string }>> {
    if (!respondForms.has(note.id)) {
        respondForms.set(note.id, useForm({ content: '' }));
    }
    return respondForms.get(note.id)!;
}

function submitResponse(note: ReservationNote): void {
    const form = getRespondForm(note);
    form.put(respond.url(note.id), { preserveScroll: true });
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

            <div v-if="reservation.cleaning_task" class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-muted-foreground">Cleaning</h3>
                    <Link :href="CleaningTaskController.show(reservation.cleaning_task).url" class="text-xs text-primary hover:underline">
                        View task
                    </Link>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span :class="['inline-block h-2.5 w-2.5 rounded-full', cleaningStatusDot(reservation.cleaning_task.status)]" />
                        <span class="text-sm font-medium">{{ cleaningStatusLabel(reservation.cleaning_task.status) }}</span>
                    </div>
                </div>
                <dl class="mt-3 space-y-1.5 text-sm">
                    <div v-if="reservation.cleaning_task.contact" class="flex justify-between">
                        <dt class="text-muted-foreground">Assigned to</dt>
                        <dd class="font-medium">{{ reservation.cleaning_task.contact.name }}</dd>
                    </div>
                    <div v-if="reservation.cleaning_task.scheduled_date" class="flex justify-between">
                        <dt class="text-muted-foreground">Date</dt>
                        <dd class="font-medium">{{ formatDate(reservation.cleaning_task.scheduled_date) }}</dd>
                    </div>
                    <div v-if="reservation.cleaning_task.started_at" class="flex justify-between">
                        <dt class="text-muted-foreground">Started</dt>
                        <dd class="font-medium">{{ formatRelativeDate(reservation.cleaning_task.started_at) }}</dd>
                    </div>
                    <div v-if="reservation.cleaning_task.completed_at" class="flex justify-between">
                        <dt class="text-muted-foreground">Completed</dt>
                        <dd class="font-medium">{{ formatRelativeDate(reservation.cleaning_task.completed_at) }}</dd>
                    </div>
                </dl>
            </div>

            <div v-if="reservation.notes" class="rounded-xl border bg-card p-5 shadow-sm">
                <h3 class="mb-2 text-sm font-medium text-muted-foreground">Notes</h3>
                <p class="whitespace-pre-wrap text-sm">{{ reservation.notes }}</p>
            </div>

            <div v-if="reservation.reservation_notes?.length" class="rounded-xl border bg-card p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-medium text-muted-foreground">Agent Notes</h3>
                <div class="space-y-4">
                    <div
                        v-for="note in reservation.reservation_notes"
                        :key="note.id"
                        class="border-b pb-4 last:border-b-0 last:pb-0"
                    >
                        <div class="mb-1 flex items-center gap-2">
                            <span v-if="note.from_agent" class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                {{ note.from_agent }}
                            </span>
                            <span
                                v-if="note.needs_response && !note.responded_at"
                                class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                            >
                                Pending response
                            </span>
                            <span
                                v-else-if="note.responded_at"
                                class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                            >
                                Responded
                            </span>
                            <time class="text-xs text-muted-foreground">{{ formatRelativeDate(note.created_at) }}</time>
                        </div>

                        <p class="whitespace-pre-wrap text-sm">{{ note.content }}</p>

                        <div v-for="reply in note.replies" :key="reply.id" class="mt-2 rounded-md border bg-muted/50 p-3">
                            <div class="mb-1 text-xs font-medium text-muted-foreground">Your response · {{ formatRelativeDate(reply.created_at) }}</div>
                            <p class="whitespace-pre-wrap text-sm">{{ reply.content }}</p>
                        </div>

                        <div v-if="note.needs_response && !note.responded_at" class="mt-3">
                            <form @submit.prevent="submitResponse(note)">
                                <Textarea
                                    v-model="getRespondForm(note).content"
                                    rows="3"
                                    class="mb-2"
                                    placeholder="Write your response..."
                                />
                                <InputError :message="getRespondForm(note).errors.content" />
                                <Button type="submit" size="sm" :disabled="getRespondForm(note).processing" class="mt-1">
                                    Respond
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
