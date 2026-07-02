<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

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

// Группировка разрешений для удобного вывода списком по категориям
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
    form.post(route('roles.store'), {
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
        <DialogContent class="max-w-2xl max-h-[85vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Создание новой роли</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6 py-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Название роли</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full px-3 py-2 border rounded-md"
                        placeholder="Например: editor"
                    />
                    <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-4">
                    <label class="text-sm font-medium block">Доступные разрешения</label>
                    <p v-if="form.errors.permissions" class="text-sm text-red-600">{{ form.errors.permissions }}</p>

                    <div v-for="(perms, group) in groupedPermissions" :key="group" class="border rounded-lg p-4 space-y-3">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ group }}</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="perm in perms" :key="perm.id" class="flex items-center space-x-2 text-sm cursor-pointer">
                                <input
                                    type="checkbox"
                                    :value="perm.id"
                                    v-model="form.permissions"
                                    class="rounded border-gray-300"
                                />
                                <span>{{ perm.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <DialogFooter class="pt-4 border-t">
                    <button type="button" @click="isOpen = false" class="px-4 py-2 border rounded-md text-sm">Отмена</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm disabled:opacity-50">
                        {{ form.processing ? 'Сохранение...' : 'Создать роль' }}
                    </button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
