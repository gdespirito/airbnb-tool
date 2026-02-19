<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import CleaningTaskController from '@/actions/App/Http/Controllers/CleaningTaskController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Property } from '@/types/models';

defineProps<{
    properties: Pick<Property, 'id' | 'name' | 'slug'>[];
    statuses: { value: string; label?: string }[];
    cleaningTypes: { value: string; label?: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Cleaning Tasks', href: CleaningTaskController.index().url },
    { title: 'New Task', href: CleaningTaskController.create().url },
];
</script>

<template>
    <Head title="New Cleaning Task" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6">
            <Heading title="New Cleaning Task" description="Schedule a cleaning task" />

            <Form
                v-bind="CleaningTaskController.store.form()"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="property_id">Property</Label>
                        <select
                            id="property_id"
                            name="property_id"
                            required
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs focus-visible:ring-[3px] focus-visible:outline-none dark:bg-input/30"
                        >
                            <option value="">Select a property</option>
                            <option v-for="property in properties" :key="property.id" :value="property.id">
                                {{ property.name }}
                            </option>
                        </select>
                        <InputError :message="errors.property_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="cleaning_type">Type</Label>
                        <select
                            id="cleaning_type"
                            name="cleaning_type"
                            required
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs focus-visible:ring-[3px] focus-visible:outline-none dark:bg-input/30"
                        >
                            <option v-for="type in cleaningTypes" :key="type.value" :value="type.value">
                                {{ type.value.replace('_', ' ') }}
                            </option>
                        </select>
                        <InputError :message="errors.cleaning_type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs focus-visible:ring-[3px] focus-visible:outline-none dark:bg-input/30"
                        >
                            <option v-for="status in statuses" :key="status.value" :value="status.value">
                                {{ status.value.replace('_', ' ') }}
                            </option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="scheduled_date">Scheduled Date</Label>
                        <Input id="scheduled_date" name="scheduled_date" type="date" required />
                        <InputError :message="errors.scheduled_date" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="cleaning_fee">Fee (CLP)</Label>
                        <Input id="cleaning_fee" name="cleaning_fee" type="number" min="0" placeholder="25000" />
                        <InputError :message="errors.cleaning_fee" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="assigned_to">Assigned To</Label>
                        <Input id="assigned_to" name="assigned_to" placeholder="Name" />
                        <InputError :message="errors.assigned_to" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="assigned_phone">Phone</Label>
                        <Input id="assigned_phone" name="assigned_phone" placeholder="+56 9 ..." />
                        <InputError :message="errors.assigned_phone" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="notes">Notes</Label>
                        <Textarea id="notes" name="notes" rows="3" placeholder="Any special instructions..." />
                        <InputError :message="errors.notes" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="processing">Create Task</Button>
                    <Link
                        :href="CleaningTaskController.index().url"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        Cancel
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
