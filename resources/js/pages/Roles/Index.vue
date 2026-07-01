<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import users from '@/routes/users';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import Button from '@/components/ui/button/Button.vue';
import { Pencil, Plus, Trash } from 'lucide-vue-next';
import { confirm } from '@/routes/two-factor';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogTrigger from '@/components/ui/dialog/DialogTrigger.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import DialogClose from '@/components/ui/dialog/DialogClose.vue';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination'
import roles from '@/routes/roles';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Роли',
        href: roles.index(),
    },
];

defineProps({
    roleList: Object,
});

</script>

<template>

    <Head title="Пользователи" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="grid grid-cols-3 content-start gap-4">
            <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div class="text-right">
                    <Link :href="users.create()">
                        <Button type="button">
                            <Plus /> Создать
                        </Button>
                    </Link>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[100px]">№</TableHead>
                            <TableHead>Роль</TableHead>
                            <TableHead class="text-right">Дейстия</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(role, index) in roleList?.data">
                            <TableCell class="font-medium">{{ `index + 1` }}</TableCell>
                            <TableCell>{{ role.name }}</TableCell>
                            <TableCell class="text-right">
                                <Link :href="users.edit(role.id)">
                                    <Button type="button" data-test="register-user-button" variant="outline">
                                        <Pencil />
                                    </Button>
                                </Link>
                                <Dialog>
                                    <DialogTrigger as-child>
                                        <Button type="button" class="ml-2" data-test="register-user-button"
                                            style="cursor: pointer;" variant="destructive">
                                            <Trash />
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent class="sm:max-w-[425px]">
                                        <DialogHeader>
                                            <DialogTitle>Удаление роли</DialogTitle>
                                            <DialogDescription>
                                                Вы действительно хотите удалить роль
                                                <b class="text-white"><i>{{ role.name }}</i></b>?
                                            </DialogDescription>
                                        </DialogHeader>
                                        <DialogFooter>
                                            <DialogClose as-child>
                                                <Button variant="outline">
                                                    Отмена
                                                </Button>
                                            </DialogClose>
                                            <DialogClose as-child>
                                                <Link :href="users.destroy(role.id)">
                                                    <Button type="submit" variant="destructive">
                                                        Удалить
                                                    </Button>
                                                </Link>
                                            </DialogClose>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div class="flex flex-col gap-6" v-if="roleList?.total > 1">
                    <Pagination v-slot="{ page }" :items-per-page="roleList?.per_page" :total="roleList?.total"
                        :default-page="1" :page="roleList?.current_page">
                        <PaginationContent v-slot="{ items }">
                            <Link :href="roleList?.prev_page_url ?? '#'">
                                <PaginationPrevious />
                            </Link>
                            <template v-for="(item, index) in items" :key="index">
                                <Link :href="roleList?.links[index + 1].url ?? '#'">
                                    <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                        :is-active="item.value === page">
                                        {{ item.value }}
                                    </PaginationItem>
                                </Link>
                            </template>
                            <PaginationEllipsis :index="4" v-if="roleList?.links.length > 5" />
                            <Link :href="roleList?.next_page_url ?? '#'">
                                <PaginationNext />
                            </Link>
                        </PaginationContent>
                    </Pagination>
                </div>

                <div class="text-right mt-10" v-if="roleList?.data.length > 10">
                    <Link :href="users.create()">
                        <Button type="button">
                            <Plus /> Создать
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
