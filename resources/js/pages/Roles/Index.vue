<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, RoleListResponse, Permission, Role } from '@/types';
import roles from '@/routes/management/roles';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Button from '@/components/ui/button/Button.vue';
import { Pencil, Plus, Trash } from 'lucide-vue-next';
import RoleCreateDialog from './Partials/RoleCreateDialog.vue';
import RoleEditDialog from './Partials/RoleEditDialog.vue';
import RoleDeleteDialog from './Partials/RoleDeleteDialog.vue';
import { usePermission } from '@/composables/usePermission';
import Pagination from '@/components/pagination/Index.vue';

const props = defineProps<{
    roleList: RoleListResponse;
    permissions: Permission[];
    guard_name: string;
    filters?: { search?: string; sort?: string; direction?: string; per_page?: number };
    perPageOptions?: number[];
}>();

const { can } = usePermission();

const breadCrumbs: BreadcrumbItem[] = [{ title: 'Роли', href: roles.index().url }];

const createDialogRef = ref<InstanceType<typeof RoleCreateDialog> | null>(null);
const editDialogRef = ref<InstanceType<typeof RoleEditDialog> | null>(null);
const deleteDialogRef = ref<InstanceType<typeof RoleDeleteDialog> | null>(null);

const currentPerPage = ref(props.filters?.per_page || 20);

watch(currentPerPage, (newVal) => {
    router.get(roles.index().url, { ...props.filters, per_page: newVal }, { preserveState: true, preserveScroll: true, replace: true });
});

watch(() => props.filters?.per_page, (newVal) => {
    if (newVal && newVal !== currentPerPage.value) {
        currentPerPage.value = newVal;
    }
});

const openEdit = (role: Role) => {
    if (!editDialogRef.value) return;
    editDialogRef.value.openDialog({
        id: role.id,
        name: role.name,
        guard_name: role.guard_name,
        permissions: role.permissions.map(p => p.id)
    });
};
</script>

<template>
    <Head title="Управление ролями" />
    <AppLayout :breadcrumbs="breadCrumbs">
        <div class="grid grid-cols-3 content-start gap-4">
            <div class="flex h-full col-span-3 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div class="text-right" v-if="can('create roles')">
                    <Button type="button" @click="createDialogRef?.openDialog()">
                        <Plus /> Добавить роль
                    </Button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[100px]">№</TableHead>
                            <TableHead>Название</TableHead>
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
                                    <span v-for="perm in role.permissions" :key="perm.id" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-800 text-zinc-200 border border-zinc-700">
                                        {{ perm.name }}
                                    </span>
                                    <span v-if="role.permissions.length === 0" class="text-zinc-500 italic text-xs">Нет разрешений</span>
                                </div>
                            </TableCell>
                            <TableCell class="text-right whitespace-nowrap space-x-2">
                                <Button v-if="can('edit roles')" type="button" variant="outline" @click="openEdit(role)">
                                    <Pencil />
                                </Button>
                                <Button v-if="can('delete roles')" type="button" variant="destructive" @click="deleteDialogRef?.openDialog(role.id, role.name)">
                                    <Trash />
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="roleList?.data.length === 0">
                            <TableCell colspan="4" class="h-24 text-center text-zinc-500">Роли не найдены</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <Pagination
                    v-model:per-page="currentPerPage"
                    :total="roleList.total"
                    :current-page="roleList.current_page"
                    :links="roleList.links"
                    :prev-page-url="roleList.prev_page_url"
                    :next-page-url="roleList.next_page_url"
                    :per-page-options="perPageOptions || [20, 30, 50]"
                />

                <div class="text-right mt-10" v-if="roleList?.data.length > 10 && can('create roles')">
                    <Button type="button" @click="createDialogRef?.openDialog()">
                        <Plus /> Добавить роль
                    </Button>
                </div>
            </div>
        </div>

        <RoleCreateDialog ref="createDialogRef" :permissions="permissions" :guard-name="guard_name" />
        <RoleEditDialog ref="editDialogRef" :permissions="permissions" />
        <RoleDeleteDialog ref="deleteDialogRef" />
    </AppLayout>
</template>
