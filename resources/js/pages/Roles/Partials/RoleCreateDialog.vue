<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';

import roles from '@/routes/roles';
import type { Permission } from '@/types';

const props = defineProps<{
    permissions: Permission[];
    guardName: string;
}>();

const isOpen = ref(false);

const form = useForm({
    name: '',
    guard_name: props.guardName,
    permissions: [] as number[],
});

const groupedPermissions = computed(() => {
    const groups: Record<string, Permission[]> = {};
    props.permissions.forEach((permission) => {
        const parts = permission.name.split(' ');
        const groupName = parts.length > 1 ? parts[1] : 'other';
        if (!groups[groupName]) groups[groupName] = [];
        groups[groupName].push(permission);
    });
    return groups;
});

const openDialog = () => {
    form.reset();
    isOpen.value = true;
};

const submit = () => {
    // Вызываем .url у сгенерированного роута
    form.post(roles.store().url, {
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
        },
    });
};

defineExpose({ openDialog });
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="max-w-2xl max-h-[85vh] overflow-y-auto bg-zinc-950 border-zinc-800 text-zinc-100">
            <DialogHeader>
                <DialogTitle>Создание новой роли</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6 py-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-300">Название роли</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full px-3 py-2 bg-zinc-900 border border-zinc-800 rounded-md text-zinc-100 focus:outline-none focus:border-zinc-700"
                        placeholder="Например: editor"
                    />
                    <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-4">
                    <label class="text-sm font-medium text-zinc-300 block">Доступные разрешения</label>
                    <div v-for="(perms, group) in groupedPermissions" :key="group" class="border border-zinc-800 rounded-lg p-4 space-y-3 bg-zinc-900/50">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ group }}</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="perm in perms" :key="perm.id" class="flex items-center space-x-2 text-sm cursor-pointer text-zinc-300 hover:text-zinc-100">
                                <input
                                    type="checkbox"
                                    :value="perm.id"
                                    v-model="form.permissions"
                                    class="rounded bg-zinc-900 border-zinc-800 text-blue-600"
                                />
                                <span>{{ perm.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <DialogFooter class="pt-4 border-t border-zinc-800 gap-2 sm:gap-0">
                    <Button type="button" variant="outline" @click="isOpen = false">Отмена</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Сохранение...' : 'Создать роль' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
