<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import users from '@/routes/users';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import Button from '@/components/ui/button/Button.vue';
import { Pencil, Plus, Trash } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';

// --- Types ---
interface User {
    id: number;
    name: string;
    email: string;
}

interface UserList {
    data: User[];
    current_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    userList: UserList;
}

// --- Props ---
const props = defineProps<Props>();

// --- State ---
const userToDelete = ref<User | null>(null);

// --- Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Пользователи',
        href: users.index(),
    },
];

// --- Methods ---
const openDeleteDialog = (user: User) => {
    userToDelete.value = user;
};

const closeDeleteDialog = () => {
    userToDelete.value = null;
};

const getPageNumber = (index: number) => {
    const offset = (props.userList.current_page - 1) * props.userList.per_page;
    return offset + index + 1;
};
</script>

<template>
    <Head title="Пользователи" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header Actions -->
            <div class="flex justify-end">
                <Link :href="users.create()">
                    <Button type="button">
                        <Plus class="mr-2 h-4 w-4" />
                        Создать
                    </Button>
                </Link>
            </div>

            <!-- Table -->
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[80px]">№</TableHead>
                        <TableHead>Имя</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead class="text-right">Действия</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(user, index) in userList.data" :key="user.id">
                        <TableCell class="font-medium">
                            {{ getPageNumber(index) }}
                        </TableCell>
                        <TableCell>{{ user.name }}</TableCell>
                        <TableCell>{{ user.email }}</TableCell>
                        <TableCell class="text-right space-x-2">
                            <Link :href="users.edit(user.id)">
                                <Button type="button" variant="outline" size="icon" data-test="edit-user-button">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                            </Link>

                            <Dialog :open="!!userToDelete">
                                <DialogTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="icon"
                                        data-test="delete-user-button"
                                        @click="openDeleteDialog(user)"
                                    >
                                        <Trash class="h-4 w-4" />
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="sm:max-w-[425px]">
                                    <DialogHeader>
                                        <DialogTitle>Удаление пользователя</DialogTitle>
                                        <DialogDescription>
                                            Вы действительно хотите удалить пользователя
                                            <span class="font-bold text-foreground">{{ userToDelete?.name }}</span>?
                                            <br/>
                                            Это действие нельзя отменить.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <DialogFooter>
                                        <Button variant="outline" @click="closeDeleteDialog">
                                            Отмена
                                        </Button>
                                        <Link
                                            :href="users.destroy(userToDelete?.id || 0)"
                                            method="delete"
                                            as="button"
                                        >
                                            <Button type="submit" variant="destructive">
                                                Удалить
                                            </Button>
                                        </Link>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </TableCell>
                    </TableRow>

                    <!-- Empty State -->
                    <TableRow v-if="!userList.data.length">
                        <TableCell :colspan="4" class="h-24 text-center">
                            Пользователи не найдены
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Pagination -->
            <div v-if="userList.total > userList.per_page" class="flex justify-between items-center mt-4">
                <div class="text-sm text-muted-foreground w-35">
                    Показано {{ userList.data.length }} из {{ userList.total }}
                </div>
                <Pagination v-slot="{ page }" :total="userList.total" :items-per-page="userList.per_page" :page="userList.current_page" class="pr-50">
                    <PaginationContent>
                        <Link :href="userList.prev_page_url || '#'">
                            <PaginationPrevious :class="{ 'pointer-events-none opacity-50': !userList.prev_page_url }" />
                        </Link>

                        <!-- Simple Page Numbers Logic -->
                        <template v-for="link in userList.links" :key="link.label">
                            <PaginationItem v-if="link.url && link.label !== '...' && !link.label.includes('&')">
                                <Link :href="link.url">
                                    <Button
                                        variant="ghost"
                                        :class="{ 'bg-muted font-bold': link.active }"
                                        size="sm"
                                    >
                                        {{ link.label }}
                                    </Button>
                                </Link>
                            </PaginationItem>
                            <PaginationEllipsis v-else-if="link.label === '...'" />
                        </template>

                        <Link :href="userList.next_page_url || '#'">
                            <PaginationNext :class="{ 'pointer-events-none opacity-50': !userList.next_page_url }" />
                        </Link>
                    </PaginationContent>
                </Pagination>
            </div>

            <!-- Footer Action (Duplicate Create) -->
            <div v-if="userList.data.length > 10" class="flex justify-end mt-6">
                <Link :href="users.create()">
                    <Button type="button" variant="secondary">
                        <Plus class="mr-2 h-4 w-4" />
                        Создать еще
                    </Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
