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
import { type CleaningTask, type Property } from '@/types/models';

const props = defineProps<{
    cleaningTask: CleaningTask;
    properties: Pick<Property, 'id' | 'name' | 'slug'>[];
    statuses: { value: string; label?: string }[];
    cleaningTypes: { value: string; label?: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Cleaning Tasks', href: CleaningTaskController.index().url },
    { title: `Task #${props.cleaningTask.id}`, href: CleaningTaskController.show(props.cleaningTask).url },
    { title: 'Edit', href: CleaningTaskController.edit(props.cleaningTask).url },
];

function formatDateForInput(date: string): string {
    return new Date(date).toISOString().split('T')[0];
}
</script>

<template>
    <Head :title="`Edit Cleaning Task #${cleaningTask.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6">
            <Heading title="Edit Cleaning Task" :description="`Task #${cleaningTask.id}`" />

            <Form
                v-bind="CleaningTaskController.update.form(cleaningTask)"
                class="space-y-6"
                v-slot="{ errors, processing, recentlySuccessful }"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="property_id">Property</Label>
                        <select
                            id="property_id"
                            name="property_id"
                            required
                            :value="cleaningTask.property_id"
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs focus-visible:ring-[3px] focus-visible:outline-none dark:bg-input/30"
                        >
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
                            :value="cleaningTask.cleaning_type"
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
                            :value="cleaningTask.status"
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
                        <Input id="scheduled_date" name="scheduled_date" type="date" :default-value="formatDateForInput(cleaningTask.scheduled_date)" required />
                        <InputError :message="errors.scheduled_date" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="cleaning_fee">Fee (CLP)</Label>
                        <Input id="cleaning_fee" name="cleaning_fee" type="number" min="0" :default-value="cleaningTask.cleaning_fee ?? ''" />
                        <InputError :message="errors.cleaning_fee" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="assigned_to">Assigned To</Label>
                        <Input id="assigned_to" name="assigned_to" :default-value="cleaningTask.assigned_to ?? ''" />
                        <InputError :message="errors.assigned_to" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="assigned_phone">Phone</Label>
                        <Input id="assigned_phone" name="assigned_phone" :default-value="cleaningTask.assigned_phone ?? ''" />
                        <InputError :message="errors.assigned_phone" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="notes">Notes</Label>
                        <Textarea id="notes" name="notes" rows="3" :default-value="cleaningTask.notes ?? ''" />
                        <InputError :message="errors.notes" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="processing">Save Changes</Button>
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p v-show="recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                    </Transition>
                    <Link
                        :href="CleaningTaskController.show(cleaningTask).url"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        Cancel
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
