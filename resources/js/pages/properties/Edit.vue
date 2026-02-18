<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Property } from '@/types/models';
import PropertyController from '@/actions/App/Http/Controllers/PropertyController';

const props = defineProps<{
    property: Property;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Properties', href: PropertyController.index().url },
    { title: props.property.name, href: PropertyController.show(props.property).url },
    { title: 'Edit', href: PropertyController.edit(props.property).url },
];
</script>

<template>
    <Head :title="`Edit ${property.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6">
            <Heading :title="`Edit ${property.name}`" description="Update property details" />

            <Form
                v-bind="PropertyController.update.form(property)"
                class="space-y-6"
                v-slot="{ errors, processing, recentlySuccessful }"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" :default-value="property.name" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="location">Location</Label>
                        <Input id="location" name="location" :default-value="property.location" required />
                        <InputError :message="errors.location" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="checkin_time">Check-in Time</Label>
                        <Input id="checkin_time" name="checkin_time" :default-value="property.checkin_time" required />
                        <InputError :message="errors.checkin_time" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="checkout_time">Check-out Time</Label>
                        <Input id="checkout_time" name="checkout_time" :default-value="property.checkout_time" required />
                        <InputError :message="errors.checkout_time" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="airbnb_url">Airbnb URL</Label>
                        <Input id="airbnb_url" name="airbnb_url" type="url" :default-value="property.airbnb_url ?? ''" />
                        <InputError :message="errors.airbnb_url" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="airbnb_listing_id">Airbnb Listing ID</Label>
                        <Input id="airbnb_listing_id" name="airbnb_listing_id" :default-value="property.airbnb_listing_id ?? ''" />
                        <InputError :message="errors.airbnb_listing_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="ical_url">iCal URL</Label>
                        <Input id="ical_url" name="ical_url" type="url" :default-value="property.ical_url ?? ''" />
                        <InputError :message="errors.ical_url" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="cleaning_contact_name">Cleaning Contact</Label>
                        <Input id="cleaning_contact_name" name="cleaning_contact_name" :default-value="property.cleaning_contact_name ?? ''" />
                        <InputError :message="errors.cleaning_contact_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="cleaning_contact_phone">Cleaning Phone</Label>
                        <Input id="cleaning_contact_phone" name="cleaning_contact_phone" :default-value="property.cleaning_contact_phone ?? ''" />
                        <InputError :message="errors.cleaning_contact_phone" />
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
                        :href="PropertyController.show(property).url"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        Cancel
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
