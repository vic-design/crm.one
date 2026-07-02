<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import roles from '@/routes/roles';

// Импортируем типы из общей папки типов
import type { BreadcrumbItem, RoleListResponse, Permission, Role } from '@/types';

// UI Компоненты таблицы
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

// UI Компоненты пагинации
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';

import Button from '@/components/ui/button/Button.vue';
import { Pencil, Plus, Trash } from 'lucide-vue-next';

// Импорт дочерних диалогов
import RoleCreateDialog from './Partials/RoleCreateDialog.vue';
import RoleEditDialog from './Partials/RoleEditDialog.vue';
import RoleDeleteDialog from './Partials/RoleDeleteDialog.vue';
import { usePermission } from '@/composables/usePermission.js';

// Пропсы теперь используют чистые глобальные типы
const props = defineProps<{
    roleList: RoleListResponse;
    permissions: Permission[];
    guard_name: string;
}>();

const { can } = usePermission();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Роли',
        href: roles.index(),
    },
];

const createDialogRef = ref<InstanceType<typeof RoleCreateDialog> | null>(null);
const editDialogRef = ref<InstanceType<typeof RoleEditDialog> | null>(null);
const deleteDialogRef = ref<InstanceType<typeof RoleDeleteDialog> | null>(null);

const openEdit = (role: Role) => {
    if (!editDialogRef.value) return;
    editDialogRef.value.openDialog({
        id: role.id,
        name: role.name,
        guard_name: role.guard_name,
        permissions: role.permissions.map(p => p.id)
    });
};

const numericLinks = computed(() => props.roleList.links.slice(1, -1));
</script>

<template>
    <Head title="Управление ролями" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="grid grid-cols-3 content-start gap-4">
            <div class="flex h-full col-span-3 flex-col gap-4 overflow-x-auto rounded-xl p-4">

                <div class="text-right" v-if="can('create roles')">
                    <Button type="button" @click="createDialogRef?.openDialog()">
                        <Plus /> Создать
                    </Button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[100px]">№</TableHead>
                            <TableHead>Роль</TableHead>
                            <TableHead>Разрешения</TableHead>
                            <TableHead class="text-right">Действия</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(role, index) in roleList?.data" :key="role.id">
                            <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                            <TableCell class="font-medium text-white">{{ role.name }}</TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1 max-w-xl">
                                    <span
                                        v-for="perm in role.permissions"
                                        :key="perm.id"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-800 text-zinc-200 border border-zinc-700"
                                    >
                                        {{ perm.name }}
                                    </span>
                                    <span v-if="role.permissions.length === 0" class="text-zinc-500 italic text-xs">
                                        Нет разрешений
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="text-right whitespace-nowrap space-x-2">

                                <Button
                                    v-if="can('edit roles')"
                                    type="button"
                                    variant="outline"
                                    @click="openEdit(role)"
                                >
                                    <Pencil />
                                </Button>

                                <Button
                                    v-if="can('delete roles')"
                                    type="button"
                                    variant="destructive"
                                    @click="deleteDialogRef?.openDialog(role.id, role.name)"
                                >
                                    <Trash />
                                </Button>

                            </TableCell>
                        </TableRow>
                        <TableRow v-if="roleList?.data.length === 0">
                            <TableCell colspan="4" class="h-24 text-center text-zinc-500">
                                Роли не найдены
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- UI Пагинация на компонентах ui/pagination -->
                <div class="flex flex-col gap-6" v-if="roleList?.total > roleList?.per_page">
                    <Pagination
                        v-slot="{ page }"
                        :items-per-page="roleList?.per_page"
                        :total="roleList?.total"
                        :default-page="1"
                        :page="roleList?.current_page"
                        class="flex justify-center"
                    >
                        <PaginationContent>

                            <!-- Кнопка "Назад" (Так как это управляющая кнопка, передаем текущую страницу минус 1 или заглушку) -->
                            <PaginationItem :value="roleList.current_page - 1 || 1">
                                <PaginationPrevious
                                    :is="roleList?.prev_page_url ? Link : 'span'"
                                    :href="roleList?.prev_page_url ?? '#'"
                                    :class="{ 'pointer-events-none opacity-50': !roleList?.prev_page_url }"
                                />
                            </PaginationItem>

                            <!-- Числовые страницы -->
                            <template v-for="(link, idx) in numericLinks" :key="idx">

                                <!-- Троеточие (Передаем фиктивный value, так как Radix требует его на уровне типов) -->
                                <PaginationItem v-if="link.label === '...'" :value="idx">
                                    <PaginationEllipsis />
                                </PaginationItem>

                                <!-- Кнопка конкретной страницы -->
                                <PaginationItem
                                    v-else
                                    :value="Number(link.label)"
                                    :is-active="link.active"
                                >
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
                            <PaginationItem :value="roleList.current_page + 1">
                                <PaginationNext
                                    :is="roleList?.next_page_url ? Link : 'span'"
                                    :href="roleList?.next_page_url ?? '#'"
                                    :class="{ 'pointer-events-none opacity-50': !roleList?.next_page_url }"
                                />
                            </PaginationItem>

                        </PaginationContent>
                    </Pagination>
                </div>


                <div class="text-right mt-10" v-if="roleList?.data.length > 10 && can('create roles')">
                    <Button type="button" @click="createDialogRef?.openDialog()">
                        <Plus /> Создать
                    </Button>
                </div>

            </div>
        </div>

        <RoleCreateDialog
            ref="createDialogRef"
            :permissions="permissions"
            :guard-name="guard_name"
        />
        <RoleEditDialog
            ref="editDialogRef"
            :permissions="permissions"
        />
        <RoleDeleteDialog
            ref="deleteDialogRef"
        />
    </AppLayout>
</template>
