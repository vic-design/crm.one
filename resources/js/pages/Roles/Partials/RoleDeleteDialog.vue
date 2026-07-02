<script setup lang="ts">
import { ref } from 'vue'; // Исправленный импорт из ядра Vue 3
import { useForm, usePage } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
    DialogClose
} from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import { AlertTriangle } from 'lucide-vue-next';

const isOpen = ref(false);
const roleId = ref<number | null>(null);
const roleName = ref('');

const form = useForm({});
const page = usePage();

const openDialog = (id: number, name: string) => {
    roleId.value = id;
    roleName.value = name;
    form.clearErrors();
    isOpen.value = true;
};

const confirmDelete = () => {
    if (!roleId.value) return;

    form.delete(route('roles.destroy', roleId.value), {
        onSuccess: () => {
            isOpen.value = false;
        },
    });
};

defineExpose({ openDialog });
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="p-2 bg-destructive/10 rounded-full text-destructive animate-pulse">
                        <AlertTriangle class="h-5 w-5" />
                    </div>
                    <DialogTitle>Удаление роли</DialogTitle>
                </div>

                <DialogDescription class="text-sm text-muted-foreground pt-2">
                    Вы действительно хотите удалить роль
                    <span class="block mt-1 text-base font-semibold text-destructive bg-destructive/5 px-2 py-1 rounded border border-destructive/20 select-all">
                        {{ roleName }}
                    </span>
                    Это действие необратимо и сотрет все связи с правами доступа.
                </DialogDescription>
            </DialogHeader>

            <div
                v-if="page.props.flash?.error"
                class="p-3 bg-destructive/10 text-destructive text-sm rounded-md border border-destructive/20 font-medium"
            >
                {{ page.props.flash.error }}
            </div>

            <DialogFooter class="gap-2 sm:gap-0 mt-4">
                <DialogClose as-child>
                    <Button variant="outline" type="button">
                        Отмена
                    </Button>
                </DialogClose>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="form.processing"
                    @click="confirmDelete"
                >
                    {{ form.processing ? 'Удаление...' : 'Удалить' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
