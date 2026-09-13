<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, User, UserListResponse, UserFilters } from '@/types';
import users from '@/routes/users';

import Input from '@/components/ui/input/Input.vue';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
    Pagination, PaginationContent, PaginationEllipsis, PaginationItem,
    PaginationNext, PaginationPrevious,
} from '@/components/ui/pagination';
import Button from '@/components/ui/button/Button.vue';
import { Pencil, Plus, Trash, ArrowUp, ArrowDown, ArrowUpDown } from 'lucide-vue-next';
import { usePermission } from '@/composables/usePermission.js';

import UserCreateDialog from './Partials/UserCreateDialog.vue';
import UserEditDialog from './Partials/UserEditDialog.vue';
import UserDeleteDialog from './Partials/UserDeleteDialog.vue';

const props = defineProps<{
    userList: UserListResponse;
    filters?: UserFilters;
}>();

const { can } = usePermission();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Пользователи', href: users.index() }];

const search = ref(props.filters?.search ?? '');
const sortColumn = ref(props.filters?.sort ?? 'name');
const sortDirection = ref<'asc' | 'desc'>(props.filters?.direction ?? 'asc');

const createDialogRef = ref<InstanceType<typeof UserCreateDialog> | null>(null);
const editDialogRef = ref<InstanceType<typeof UserEditDialog> | null>(null);
const deleteDialogRef = ref<InstanceType<typeof UserDeleteDialog> | null>(null);

const openEdit = (user: User) => {
    editDialogRef.value?.openDialog({ id: user.id, name: user.name, email: user.email });
};

const numericLinks = computed(() => props.userList.links.slice(1, -1));

const getPageNumber = (index: number) => {
    return (props.userList.current_page - 1) * props.userList.per_page + index + 1;
};

const applyFilters = () => {
    router.get(
        users.index(),
        {
            search: search.value || undefined,
            sort: sortColumn.value,
            direction: sortDirection.value,
        },
        { preserveState: true, replace: true, preserveScroll: true }
    );
};

const toggleSort = (column: string) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
    applyFilters();
};

let timeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(applyFilters, 300);
});
</script>

<template>
    <Head title="Пользователи" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="grid grid-cols-3 content-start gap-4">
            <div class="flex h-full col-span-3 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="w-full max-w-sm">
                        <Input v-model="search" type="text" placeholder="Поиск по имени или email..." class="w-full" />
                    </div>
                    <div v-if="can('create users')">
                        <Button type="button" @click="createDialogRef?.openDialog()">
                            <Plus class="mr-2 h-4 w-4" /> Создать
                        </Button>
                    </div>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[80px]">№</TableHead>
                            <TableHead class="cursor-pointer select-none hover:bg-zinc-800/50 transition-colors" @click="toggleSort('name')">
                                <div class="flex items-center gap-2">
                                    Имя
                                    <ArrowUpDown v-if="sortColumn !== 'name'" class="h-4 w-4 text-zinc-500" />
                                    <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4 text-blue-500" />
                                    <ArrowDown v-else class="h-4 w-4 text-blue-500" />
                                </div>
                            </TableHead>
                            <TableHead class="cursor-pointer select-none hover:bg-zinc-800/50 transition-colors" @click="toggleSort('email')">
                                <div class="flex items-center gap-2">
                                    Email
                                    <ArrowUpDown v-if="sortColumn !== 'email'" class="h-4 w-4 text-zinc-500" />
                                    <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4 text-blue-500" />
                                    <ArrowDown v-else class="h-4 w-4 text-blue-500" />
                                </div>
                            </TableHead>
                            <TableHead class="text-right">Действия</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(user, index) in userList?.data" :key="user.id">
                            <TableCell class="font-medium">{{ getPageNumber(index) }}</TableCell>
                            <TableCell class="font-medium text-white">{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell class="text-right whitespace-nowrap space-x-2">
                                <Button v-if="can('edit users')" type="button" variant="outline" size="icon" @click="openEdit(user)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button v-if="can('delete users')" type="button" variant="destructive" size="icon" @click="deleteDialogRef?.openDialog(user.id, user.name)">
                                    <Trash class="h-4 w-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!userList?.data.length">
                            <TableCell colspan="4" class="h-24 text-center text-zinc-500">Пользователи не найдены</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div class="flex flex-col gap-6" v-if="userList?.total > userList?.per_page">
                    <Pagination v-slot="{ page }" :items-per-page="userList?.per_page" :total="userList?.total" :default-page="1" :page="userList?.current_page" class="flex justify-center">
                        <PaginationContent>
                            <PaginationItem :value="userList.current_page - 1 || 1">
                                <Link
                                    v-if="userList?.prev_page_url"
                                    :href="userList.prev_page_url"
                                    preserve-state
                                    preserve-scroll
                                >
                                    <PaginationPrevious />
                                </Link>
                                <PaginationPrevious v-else class="pointer-events-none opacity-50" />
                            </PaginationItem>

                            <!-- Числовые страницы (без изменений) -->
                            <template v-for="(link, idx) in numericLinks" :key="idx">
                                <PaginationItem v-if="link.label === '...'" :value="idx">
                                    <PaginationEllipsis />
                                </PaginationItem>
                                <PaginationItem v-else :value="Number(link.label)" :is-active="link.active">
                                    <Link
                                        :href="link.url ?? '#'"
                                        class="inline-flex items-center justify-center rounded-md text-sm font-medium h-9 w-9 border transition-colors"
                                        :class="{
                                            'bg-blue-600 text-white border-blue-600': link.active,
                                            'bg-zinc-900 text-zinc-200 border-zinc-800 hover:bg-zinc-800': !link.active && link.url,
                                            'pointer-events-none opacity-30': !link.url
                                        }"
                                        v-html="link.label"
                                    />
                                </PaginationItem>
                            </template>

                            <!-- Кнопка "Вперед" -->
                            <PaginationItem :value="userList.current_page + 1">
                                <Link
                                    v-if="userList?.next_page_url"
                                    :href="userList.next_page_url"
                                    preserve-state
                                    preserve-scroll
                                >
                                    <PaginationNext />
                                </Link>
                                <PaginationNext v-else class="pointer-events-none opacity-50" />
                            </PaginationItem>
                        </PaginationContent>
                    </Pagination>
                </div>

                <div class="text-right mt-10" v-if="userList?.data.length > 10 && can('create users')">
                    <Button type="button" @click="createDialogRef?.openDialog()">
                        <Plus class="mr-2 h-4 w-4" /> Создать
                    </Button>
                </div>
            </div>
        </div>

        <UserCreateDialog ref="createDialogRef" />
        <UserEditDialog ref="editDialogRef" />
        <UserDeleteDialog ref="deleteDialogRef" />
    </AppLayout>
</template>
