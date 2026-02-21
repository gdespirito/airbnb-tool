<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Contact } from '@/types/models';

defineProps<{
    contacts: Pick<Contact, 'id' | 'name'>[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Properties', href: PropertyController.index().url },
    { title: 'New Property', href: PropertyController.create().url },
];
</script>

<template>
    <Head title="New Property" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6">
            <Heading title="New Property" description="Add a new property to manage" />

            <Form
                v-bind="PropertyController.store.form()"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" required placeholder="e.g. Casa Pupuya" />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="location">Location</Label>
                        <Input id="location" name="location" required placeholder="e.g. Pupuya, O'Higgins, Chile" />
                        <InputError :message="errors.location" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="checkin_time">Check-in Time</Label>
                        <Input id="checkin_time" name="checkin_time" default-value="15:00" required placeholder="15:00" />
                        <InputError :message="errors.checkin_time" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="checkout_time">Check-out Time</Label>
                        <Input id="checkout_time" name="checkout_time" default-value="12:00" required placeholder="12:00" />
                        <InputError :message="errors.checkout_time" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="airbnb_url">Airbnb URL</Label>
                        <Input id="airbnb_url" name="airbnb_url" type="url" placeholder="https://airbnb.com/rooms/..." />
                        <InputError :message="errors.airbnb_url" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="airbnb_listing_id">Airbnb Listing ID</Label>
                        <Input id="airbnb_listing_id" name="airbnb_listing_id" placeholder="e.g. 16897504" />
                        <InputError :message="errors.airbnb_listing_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="ical_url">iCal URL</Label>
                        <Input id="ical_url" name="ical_url" type="url" placeholder="https://..." />
                        <InputError :message="errors.ical_url" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="cleaning_contact_id">Cleaning Contact</Label>
                        <select id="cleaning_contact_id" name="cleaning_contact_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">None</option>
                            <option v-for="contact in contacts" :key="contact.id" :value="contact.id">{{ contact.name }}</option>
                        </select>
                        <InputError :message="errors.cleaning_contact_id" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="processing">Create Property</Button>
                    <Link
                        :href="PropertyController.index().url"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        Cancel
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
