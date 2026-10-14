<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import tags from '@/routes/tags';

const isOpen = ref(false);
const tagId = ref<number | null>(null);
const tagName = ref('');

const openDialog = (id: number, name: string) => {
    tagId.value = id;
    tagName.value = name;
    isOpen.value = true;
};

const confirmDelete = () => {
    if (!tagId.value) return;

    router.delete(tags.destroy.url({ tag: tagId.value }), {
        onSuccess: () => {
            isOpen.value = false;
        },
    });
};

defineExpose({ openDialog });
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-[425px] bg-zinc-950 border-zinc-800 text-zinc-100">
            <DialogHeader>
                <DialogTitle>Удаление тега</DialogTitle>
                <DialogDescription class="text-zinc-400">
                    Вы уверены, что хотите удалить тег <strong class="text-white">{{ tagName }}</strong>? Это действие необратимо.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="pt-4 border-t border-zinc-800 justify-between sm:justify-between">
                <Button type="button" variant="outline" @click="isOpen = false">Отмена</Button>
                <Button type="button" variant="destructive" @click="confirmDelete">
                    Удалить
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
