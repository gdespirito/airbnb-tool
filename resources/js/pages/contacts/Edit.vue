<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ContactController from '@/actions/App/Http/Controllers/ContactController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Contact } from '@/types/models';

const props = defineProps<{
    contact: Contact;
    roles: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Contacts', href: ContactController.index().url },
    { title: props.contact.name, href: ContactController.show(props.contact).url },
    { title: 'Edit', href: ContactController.edit(props.contact).url },
];
</script>

<template>
    <Head :title="`Edit ${contact.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6">
            <Heading title="Edit Contact" :description="contact.name" />

            <Form
                v-bind="ContactController.update.form(contact)"
                class="space-y-6"
                v-slot="{ errors, processing, recentlySuccessful }"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" required :default-value="contact.name" />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input id="phone" name="phone" :default-value="contact.phone ?? ''" />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" type="email" :default-value="contact.email ?? ''" />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="role">Role</Label>
                        <select
                            id="role"
                            name="role"
                            required
                            :value="contact.role"
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs focus-visible:ring-[3px] focus-visible:outline-none dark:bg-input/30"
                        >
                            <option v-for="role in roles" :key="role" :value="role">
                                {{ role }}
                            </option>
                        </select>
                        <InputError :message="errors.role" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="notes">Notes</Label>
                        <Textarea id="notes" name="notes" rows="3" :default-value="contact.notes ?? ''" />
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
                        :href="ContactController.show(contact).url"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        Cancel
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
