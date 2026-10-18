<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, User, UserListResponse, UserFilters, Tag } from '@/types';
import users from '@/routes/management/users';
import Input from '@/components/ui/input/Input.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import Button from '@/components/ui/button/Button.vue';
import { Pencil, Plus, Trash, ArrowUp, ArrowDown, ArrowUpDown, X } from 'lucide-vue-next';
import { usePermission } from '@/composables/usePermission';
import { useTable } from '@/composables/useTable';

// Компоненты фильтров
import RoleFilter from '@/components/Filters/RoleFilter.vue';
import TagFilter from '@/components/Filters/TagFilter.vue';

// Диалоги
import UserCreateDialog from './Partials/UserCreateDialog.vue';
import UserEditDialog from './Partials/UserEditDialog.vue';
import UserDeleteDialog from './Partials/UserDeleteDialog.vue';

// компонент пагинации
import Pagination from '@/components/pagination/Index.vue';

const props = defineProps<{
    userList: UserListResponse;
    availableRoles: { id: number; name: string }[];
    availableTags: Tag[];
    filters?: UserFilters;
    perPageOptions?: number[];
}>();

const { can } = usePermission();

const breadCrumbs: BreadcrumbItem[] = [
    { title: 'Пользователи', href: users.index().url },
];

const {
    filters,
    sortColumn,
    sortDirection,
    toggleSort,
    resetFilters,
} = useTable<UserFilters>(users.index().url, props.filters);

const selectedRoles = ref<string[]>(props.filters?.roles ?? []);
const selectedTags = ref<number[]>(props.filters?.tags ?? []);
const currentPerPage = ref(props.userList.per_page || 20);
const currentUser = ref<User | null>(null); // ← Добавлено для UserEditDialog

// Синхронизация фильтров с локальным состоянием
watch(selectedRoles, (newVal) => {
    filters.value.roles = newVal.length > 0 ? newVal : undefined;
});

watch(selectedTags, (newVal) => {
    filters.value.tags = newVal.length > 0 ? newVal : undefined;
});

watch(currentPerPage, (newVal) => {
    filters.value.per_page = newVal;
});

// Синхронизация обратно, если фильтры были сброшены извне
watch(() => props.filters?.per_page, (newVal) => {
    if (newVal && newVal !== currentPerPage.value) {
        currentPerPage.value = newVal;
    }
});

const hasActiveFilters = computed(() => {
    return !!(filters.value.search || selectedRoles.value.length > 0 || selectedTags.value.length > 0);
});

const resetAllFilters = () => {
    selectedRoles.value = [];
    selectedTags.value = [];
    resetFilters();
};

const createDialogRef = ref<InstanceType<typeof UserCreateDialog> | null>(null);
const editDialogRef = ref<InstanceType<typeof UserEditDialog> | null>(null);
const deleteDialogRef = ref<InstanceType<typeof UserDeleteDialog> | null>(null);

const openEdit = (user: User) => {
    currentUser.value = user;
    editDialogRef.value?.openDialog(user);
};

const getPageNumber = (index: number) => {
    return (props.userList.current_page - 1) * props.userList.per_page + index + 1;
};
</script>

<template>
    <Head title="Пользователи" />
    <AppLayout :breadcrumbs="breadCrumbs">
        <div class="p-6 space-y-6">
            <!-- Заголовок и кнопка создания -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h1 class="text-2xl font-bold text-white">Пользователи</h1>
                <Button v-if="can('create users')" @click="createDialogRef?.openDialog()">
                    <Plus class="mr-2 h-4 w-4" />
                    Создать
                </Button>
            </div>

            <!-- Фильтры -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full">
                <div class="w-full sm:w-auto sm:flex-1 max-w-md">
                    <Input
                        v-model="filters.search"
                        type="text"
                        placeholder="Поиск по имени или email..."
                        class="w-full h-9"
                    />
                </div>
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
                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="sm"
                        class="h-9 text-zinc-400 hover:text-zinc-100"
                        @click="resetAllFilters"
                    >
                        <X class="mr-1.5 h-3.5 w-3.5" />
                        Сбросить всё
                    </Button>
                </div>
            </div>

            <!-- Таблица -->
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
                        <TableHead>Роли</TableHead>
                        <TableHead>Теги</TableHead>
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
                        <TableCell>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-600/20 text-blue-300 border border-blue-500/30"
                                >
                                    {{ role.name }}
                                </span>
                                <span v-if="!user.roles || user.roles.length === 0" class="text-zinc-500 text-xs italic">
                                    Нет ролей
                                </span>
                            </div>
                        </TableCell>
                        <TableCell>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="tag in user.tags"
                                    :key="tag.id"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border"
                                    :style="{
                                        backgroundColor: tag.color ? tag.color + '20' : '#3f3f46',
                                        color: tag.color || '#d4d4d8',
                                        borderColor: tag.color ? tag.color + '50' : '#52525b'
                                    }"
                                >
                                    {{ tag.name }}
                                </span>
                                <span v-if="!user.tags || user.tags.length === 0" class="text-zinc-500 text-xs italic">
                                    Нет тегов
                                </span>
                            </div>
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
                        <TableCell colspan="6" class="h-24 text-center text-zinc-500">
                            Пользователи не найдены
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Пагинация -->
            <Pagination
                v-model:per-page="currentPerPage"
                :total="userList.total"
                :current-page="userList.current_page"
                :links="userList.links"
                :prev-page-url="userList.prev_page_url"
                :next-page-url="userList.next_page_url"
                :per-page-options="perPageOptions || [20, 30, 50]"
            />

            <!-- Диалоги -->
            <UserCreateDialog ref="createDialogRef" :available-roles="availableRoles" :available-tags="availableTags" />
            <UserEditDialog
                ref="editDialogRef"
                :user="currentUser"
                :available-roles="availableRoles"
                :available-tags="availableTags"
            />
            <UserDeleteDialog ref="deleteDialogRef" />
        </div>
    </AppLayout>
</template>
