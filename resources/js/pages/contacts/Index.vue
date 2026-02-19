<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ContactController from '@/actions/App/Http/Controllers/ContactController';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Contact } from '@/types/models';

defineProps<{
    contacts: Contact[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Contacts', href: ContactController.index().url },
];

function roleColor(role: string): string {
    const colors: Record<string, string> = {
        cleaning: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        handyman: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        other: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return colors[role] ?? '';
}
</script>

<template>
    <Head title="Contacts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Contacts</h1>
                <Link
                    :href="ContactController.create().url"
                    class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
                >
                    Add Contact
                </Link>
            </div>

            <div v-if="contacts.length === 0" class="rounded-xl border border-dashed p-12 text-center text-muted-foreground">
                No contacts yet.
            </div>

            <div v-else class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Phone</th>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">Role</th>
                            <th class="px-4 py-3 text-right font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="contact in contacts" :key="contact.id" class="hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium">
                                <Link :href="ContactController.show(contact).url" class="text-primary hover:underline">
                                    {{ contact.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3">{{ contact.phone ?? '-' }}</td>
                            <td class="px-4 py-3">{{ contact.email ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span :class="['rounded-full px-2 py-0.5 text-xs font-medium capitalize', roleColor(contact.role)]">
                                    {{ contact.role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="ContactController.show(contact).url" class="text-primary hover:underline">
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
