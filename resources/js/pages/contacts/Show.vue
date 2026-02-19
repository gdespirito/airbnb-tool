<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import CleaningTaskController from '@/actions/App/Http/Controllers/CleaningTaskController';
import ContactController from '@/actions/App/Http/Controllers/ContactController';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Contact } from '@/types/models';

const props = defineProps<{
    contact: Contact;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Contacts', href: ContactController.index().url },
    { title: props.contact.name, href: ContactController.show(props.contact).url },
];

function roleColor(role: string): string {
    const colors: Record<string, string> = {
        cleaning: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        handyman: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        other: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return colors[role] ?? '';
}

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

function deleteContact(): void {
    if (confirm('Are you sure you want to delete this contact?')) {
        router.delete(ContactController.destroy(props.contact).url);
    }
}
</script>

<template>
    <Head :title="contact.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ contact.name }}</h1>
                    <div class="mt-1">
                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', roleColor(contact.role)]">
                            {{ contact.role }}
                        </span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="ContactController.edit(contact).url"
                        class="inline-flex items-center rounded-md border bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent"
                    >
                        Edit
                    </Link>
                    <button
                        @click="deleteContact"
                        class="inline-flex items-center rounded-md border border-red-200 bg-background px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 dark:border-red-800 dark:hover:bg-red-950"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border bg-card p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-medium text-muted-foreground">Contact Info</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Phone</dt>
                            <dd class="font-medium">{{ contact.phone ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Email</dt>
                            <dd class="font-medium">{{ contact.email ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Role</dt>
                            <dd class="font-medium capitalize">{{ contact.role }}</dd>
                        </div>
                    </dl>
                </div>

                <div v-if="contact.notes" class="rounded-xl border bg-card p-5 shadow-sm">
                    <h3 class="mb-2 text-sm font-medium text-muted-foreground">Notes</h3>
                    <p class="whitespace-pre-wrap text-sm">{{ contact.notes }}</p>
                </div>
            </div>

            <div v-if="contact.properties && contact.properties.length > 0" class="rounded-xl border bg-card p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-medium text-muted-foreground">Assigned Properties</h3>
                <ul class="space-y-2 text-sm">
                    <li v-for="property in contact.properties" :key="property.id">
                        <Link :href="PropertyController.show(property).url" class="text-primary hover:underline">
                            {{ property.name }}
                        </Link>
                    </li>
                </ul>
            </div>

            <div v-if="contact.cleaning_tasks && contact.cleaning_tasks.length > 0" class="overflow-hidden rounded-xl border">
                <div class="border-b bg-muted/50 px-4 py-3">
                    <h3 class="text-sm font-medium">Cleaning Tasks</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/30">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium">Date</th>
                            <th class="px-4 py-2 text-left font-medium">Property</th>
                            <th class="px-4 py-2 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="task in contact.cleaning_tasks" :key="task.id" class="hover:bg-muted/30">
                            <td class="px-4 py-2">
                                <Link :href="CleaningTaskController.show(task).url" class="text-primary hover:underline">
                                    {{ formatDate(task.scheduled_date) }}
                                </Link>
                            </td>
                            <td class="px-4 py-2">{{ task.property?.name ?? '-' }}</td>
                            <td class="px-4 py-2">
                                <span :class="['rounded-full px-2 py-0.5 text-xs font-medium capitalize', statusColor(task.status)]">
                                    {{ task.status.replace('_', ' ') }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
