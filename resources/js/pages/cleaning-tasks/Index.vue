<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CleaningTaskController from '@/actions/App/Http/Controllers/CleaningTaskController';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type CleaningTask } from '@/types/models';

defineProps<{
    cleaningTasks: CleaningTask[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Cleaning Tasks', href: CleaningTaskController.index().url },
];

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('es-CL', { day: 'numeric', month: 'short', year: 'numeric' });
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
</script>

<template>
    <Head title="Cleaning Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Cleaning Tasks</h1>
                <Link
                    :href="CleaningTaskController.create().url"
                    class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
                >
                    Add Task
                </Link>
            </div>

            <div v-if="cleaningTasks.length === 0" class="rounded-xl border border-dashed p-12 text-center text-muted-foreground">
                No upcoming cleaning tasks.
            </div>

            <div v-else class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Property</th>
                            <th class="px-4 py-3 text-left font-medium">Type</th>
                            <th class="px-4 py-3 text-left font-medium">Assigned To</th>
                            <th class="px-4 py-3 text-left font-medium">Fee</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="task in cleaningTasks" :key="task.id" class="hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium">{{ formatDate(task.scheduled_date) }}</td>
                            <td class="px-4 py-3">
                                <Link
                                    v-if="task.property"
                                    :href="PropertyController.show(task.property).url"
                                    class="text-primary hover:underline"
                                >
                                    {{ task.property.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3">{{ typeLabel(task.cleaning_type) }}</td>
                            <td class="px-4 py-3">{{ task.assigned_to ?? '-' }}</td>
                            <td class="px-4 py-3">{{ formatFee(task.cleaning_fee) }}</td>
                            <td class="px-4 py-3">
                                <span :class="['rounded-full px-2 py-0.5 text-xs font-medium capitalize', statusColor(task.status)]">
                                    {{ task.status.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="CleaningTaskController.show(task).url" class="text-primary hover:underline">
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
