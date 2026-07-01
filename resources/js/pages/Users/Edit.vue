<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import users from '@/routes/users';
import Button from '@/components/ui/button/Button.vue';
import { Link2, Pencil, Trash } from 'lucide-vue-next';
import UsersController from '@/actions/App/Http/Controllers/UsersController';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Пользователи',
        href: users.index(),
    },
    {
        title: 'Редактировать пользователя',
        href: '#',
    },
];

defineProps({
    user: Object,
    errors: Object,
});
</script>

<template>

    <Head title="Редактировать пользователя" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="grid grid-cols-3 content-start gap-4">
            <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Form v-bind="UsersController.update.form(user?.id)" class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }">
                    <input name="id" :value='user?.id' type="hidden">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" class="mt-1 block w-full" name="name" :default-value="user?.name" required
                                autocomplete="name" placeholder="Full name" />
                            <InputError class="mt-2" :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email address</Label>
                            <Input id="email" type="email" class="mt-1 block w-full" name="email"
                                :default-value="user?.email" required autocomplete="username"
                                placeholder="Email address" />
                            <InputError class="mt-2" :message="errors.email" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Link :href="users.index()">
                                <Button :disabled="processing" data-test="update-profile-button" type="button"
                                    variant="outline">Назад</Button>
                            </Link>

                            <Button :disabled="processing" data-test="update-profile-button"
                                type="submit">Сохранить</Button>

                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-show="recentlySuccessful" class="text-sm text-neutral-600">
                                    Готово!
                                </p>
                            </Transition>
                        </div>
                </Form>
            </div>
        </div>
    </AppLayout>
</template>
