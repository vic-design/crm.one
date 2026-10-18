<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { User, UserListResponse, UserFilters, Tag, BreadcrumbItem } from '@/types';
import users from '@/routes/management/users';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
    Pagination, PaginationContent, PaginationEllipsis, PaginationItem,
    PaginationNext, PaginationPrevious,
} from '@/components/ui/pagination';
import { Pencil, Plus, Trash, ArrowUp, ArrowDown, ArrowUpDown, X } from 'lucide-vue-next';
import { usePermission } from '@/composables/usePermission';
import { useTable } from '@/composables/useTable';
import UserCreateDialog from './Partials/UserCreateDialog.vue';
import UserEditDialog from './Partials/UserEditDialog.vue';
import UserDeleteDialog from './Partials/UserDeleteDialog.vue';
import RoleFilter from '@/components/Filters/RoleFilter.vue';
import TagFilter from '@/components/Filters/TagFilter.vue';

const props = defineProps<{
    userList: UserListResponse;
    availableRoles: { id: number; name: string }[];
    availableTags: Tag[];
    filters?: UserFilters;
}>();

const { can } = usePermission();

const breadCrumbItems: BreadcrumbItem[] = [
    { title: 'Пользователи', href: users.index() }
];

const {
    filters,
    sortColumn,
    sortDirection,
    toggleSort,
    resetFilters,
} = useTable<UserFilters>(users.index().url, props.filters);

// Состояние фильтров
const selectedRoles = ref<string[]>(props.filters?.roles ?? []);
const selectedTags = ref<number[]>(props.filters?.tags ?? []);

// Синхронизация выбранных значений с объектом filters
watch(selectedRoles, (newVal) => {
    filters.value.roles = newVal.length > 0 ? newVal : undefined;
});

watch(selectedTags, (newVal) => {
    filters.value.tags = newVal.length > 0 ? newVal : undefined;
});

// Проверка наличия активных фильтров
const hasActiveFilters = computed(() => {
    return !!filters.value.search || selectedRoles.value.length > 0 || selectedTags.value.length > 0;
});

// Сброс всех фильтров
const resetAllFilters = () => {
    selectedRoles.value = [];
    selectedTags.value = [];
    filters.value.search = '';
    resetFilters();
};

// Диалоги
const createDialogRef = ref<InstanceType<typeof UserCreateDialog> | null>(null);
const editDialogRef = ref<InstanceType<typeof UserEditDialog> | null>(null);
const deleteDialogRef = ref<InstanceType<typeof UserDeleteDialog> | null>(null);
const currentUser = ref<User | null>(null);

const openEdit = (user: User) => {
    currentUser.value = user;
    editDialogRef.value?.openDialog(user);
};

// Числовые ссылки для пагинации (исключаем first и last)
const numericLinks = computed(() => props.userList.links.slice(1, -1));

// Номер страницы для отображения в таблице
const getPageNumber = (index: number) => {
    return (props.userList.current_page - 1) * props.userList.per_page + index + 1;
};
</script>

<template>
    <Head title="Пользователи" />
    <AppLayout :breadCrumbItems="breadCrumbItems">
        <div class="p-6 space-y-6">

            <!-- Верхняя панель: Фильтры и кнопка создания -->
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <!-- Поиск -->
                <div class="w-full lg:w-auto lg:flex-1 max-w-md">
                    <Input
                        v-model="filters.search"
                        type="text"
                        placeholder="Поиск по имени или email..."
                        class="h-9"
                    />
                </div>

                <!-- Компоненты фильтров -->
                <div class="flex items-center gap-2 flex-wrap">
                    <RoleFilter
                        v-model="selectedRoles"
                        :options="availableRoles"
                        label="Роли"
                    />

                    <TagFilter
                        v-model="selectedTags"
                        :options="availableTags"
                        label="Теги"
                    />

                    <!-- Кнопка сброса (появляется только при активных фильтрах) -->
                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="sm"
                        class="h-9 text-zinc-400 hover:text-zinc-100 transition-opacity"
                        @click="resetAllFilters"
                    >
                        <X class="mr-1.5 h-3.5 w-3.5" />
                        Сбросить
                    </Button>
                </div>

                <!-- Распорка и кнопка создания -->
                <div class="flex-1 hidden lg:block"></div>

                <Button v-if="can('create users')" @click="createDialogRef?.openDialog()" class="h-9">
                    <Plus class="mr-2 h-4 w-4" />
                    Создать
                </Button>
            </div>

            <!-- Таблица пользователей -->
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[80px]">№</TableHead>
                        <TableHead
                            class="cursor-pointer select-none hover:bg-zinc-800/50 transition-colors"
                            @click="toggleSort('name')"
                        >
                            <div class="flex items-center gap-2">
                                Имя
                                <ArrowUpDown v-if="sortColumn !== 'name'" class="h-4 w-4 text-zinc-500" />
                                <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4 text-blue-500" />
                                <ArrowDown v-else class="h-4 w-4 text-blue-500" />
                            </div>
                        </TableHead>
                        <TableHead
                            class="cursor-pointer select-none hover:bg-zinc-800/50 transition-colors"
                            @click="toggleSort('email')"
                        >
                            <div class="flex items-center gap-2">
                                Email
                                <ArrowUpDown v-if="sortColumn !== 'email'" class="h-4 w-4 text-zinc-500" />
                                <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4 text-blue-500" />
                                <ArrowDown v-else class="h-4 w-4 text-blue-500" />
                            </div>
                        </TableHead>
                        <TableHead class="text-right w-[120px]">Действия</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(user, index) in userList.data" :key="user.id">
                        <TableCell class="font-medium text-zinc-400">
                            {{ getPageNumber(index) }}
                        </TableCell>
                        <TableCell class="font-medium text-white">
                            <Link
                                :href="users.show.url({ user: user.id })"
                                class="hover:text-blue-400 transition-colors"
                            >
                                {{ user.name }}
                            </Link>
                        </TableCell>
                        <TableCell class="text-zinc-300">
                            {{ user.email }}
                        </TableCell>
                        <TableCell class="text-right whitespace-nowrap space-x-2">
                            <Button
                                v-if="can('edit users')"
                                type="button"
                                variant="outline"
                                size="icon"
                                class="h-8 w-8"
                                @click="openEdit(user)"
                            >
                                <Pencil class="h-4 w-4" />
                            </Button>
                            <Button
                                v-if="can('delete users')"
                                type="button"
                                variant="destructive"
                                size="icon"
                                class="h-8 w-8"
                                @click="deleteDialogRef?.openDialog(user.id, user.name)"
                            >
                                <Trash class="h-4 w-4" />
                            </Button>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="userList.data.length === 0">
                        <TableCell colspan="4" class="h-24 text-center text-zinc-500">
                            Пользователи не найдены
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Пагинация -->
            <div v-if="Math.ceil(userList.total / userList.per_page) > 1" class="flex justify-center">
                <Pagination
                    v-slot="{ page }"
                    :items-per-page="userList.per_page"
                    :total="userList.total"
                    :default-page="1"
                    :page="userList.current_page"
                    class="flex justify-center"
                >
                    <PaginationContent>
                        <!-- Previous -->
                        <PaginationItem :value="userList.current_page - 1 || 1">
                            <Link v-if="userList.prev_page_url" :href="userList.prev_page_url" preserve-state preserve-scroll>
                                <PaginationPrevious />
                            </Link>
                            <PaginationPrevious v-else class="pointer-events-none opacity-50" />
                        </PaginationItem>

                        <!-- Numbers -->
                        <template v-for="(link, idx) in numericLinks" :key="idx">
                            <PaginationItem v-if="link.label === '...'" :value="idx">
                                <PaginationEllipsis />
                            </PaginationItem>
                            <PaginationItem v-else :value="Number(link.label)" :is-active="link.active">
                                <Link
                                    :href="link.url || '#'"
                                    preserve-state
                                    preserve-scroll
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

                        <!-- Next -->
                        <PaginationItem :value="userList.current_page + 1">
                            <Link v-if="userList.next_page_url" :href="userList.next_page_url" preserve-state preserve-scroll>
                                <PaginationNext />
                            </Link>
                            <PaginationNext v-else class="pointer-events-none opacity-50" />
                        </PaginationItem>
                    </PaginationContent>
                </Pagination>
            </div>

            <!-- Диалоги -->
            <UserCreateDialog ref="createDialogRef" :available-roles="availableRoles" />
            <UserEditDialog ref="editDialogRef" :user="currentUser" :available-roles="availableRoles" />
            <UserDeleteDialog ref="deleteDialogRef" />
        </div>
    </AppLayout>
</template>
