<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Property, type Reservation } from '@/types/models';
import ReservationController from '@/actions/App/Http/Controllers/ReservationController';

const props = defineProps<{
    reservation: Reservation;
    properties: Pick<Property, 'id' | 'name' | 'slug'>[];
    statuses: { value: string; label?: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reservations', href: ReservationController.index().url },
    { title: props.reservation.guest_name, href: ReservationController.show(props.reservation).url },
    { title: 'Edit', href: ReservationController.edit(props.reservation).url },
];

function formatDateForInput(date: string): string {
    return new Date(date).toISOString().split('T')[0];
}
</script>

<template>
    <Head :title="`Edit - ${reservation.guest_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6">
            <Heading :title="`Edit Reservation`" :description="reservation.guest_name" />

            <Form
                v-bind="ReservationController.update.form(reservation)"
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
                            :value="reservation.property_id"
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs focus-visible:ring-[3px] focus-visible:outline-none dark:bg-input/30"
                        >
                            <option v-for="property in properties" :key="property.id" :value="property.id">
                                {{ property.name }}
                            </option>
                        </select>
                        <InputError :message="errors.property_id" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="guest_name">Guest Name</Label>
                        <Input id="guest_name" name="guest_name" :default-value="reservation.guest_name" required />
                        <InputError :message="errors.guest_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="guest_phone">Phone</Label>
                        <Input id="guest_phone" name="guest_phone" :default-value="reservation.guest_phone ?? ''" />
                        <InputError :message="errors.guest_phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="guest_email">Email</Label>
                        <Input id="guest_email" name="guest_email" type="email" :default-value="reservation.guest_email ?? ''" />
                        <InputError :message="errors.guest_email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="check_in">Check-in</Label>
                        <Input id="check_in" name="check_in" type="date" :default-value="formatDateForInput(reservation.check_in)" required />
                        <InputError :message="errors.check_in" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="check_out">Check-out</Label>
                        <Input id="check_out" name="check_out" type="date" :default-value="formatDateForInput(reservation.check_out)" required />
                        <InputError :message="errors.check_out" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="number_of_guests">Number of Guests</Label>
                        <Input id="number_of_guests" name="number_of_guests" type="number" min="1" max="20" :default-value="reservation.number_of_guests" required />
                        <InputError :message="errors.number_of_guests" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            required
                            :value="reservation.status"
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs focus-visible:ring-[3px] focus-visible:outline-none dark:bg-input/30"
                        >
                            <option v-for="status in statuses" :key="status.value" :value="status.value">
                                {{ status.value.replace('_', ' ') }}
                            </option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="airbnb_reservation_id">Airbnb Reservation ID</Label>
                        <Input id="airbnb_reservation_id" name="airbnb_reservation_id" :default-value="reservation.airbnb_reservation_id ?? ''" />
                        <InputError :message="errors.airbnb_reservation_id" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="notes">Notes</Label>
                        <Textarea id="notes" name="notes" rows="3" :default-value="reservation.notes ?? ''" />
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
                        :href="ReservationController.show(reservation).url"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        Cancel
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
