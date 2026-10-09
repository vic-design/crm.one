<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';
import tags from '@/routes/tags';

const isOpen = ref(false);

const form = useForm({
    name: '',
    color: '#3b82f6',
});

const openDialog = () => {
    form.reset();
    form.clearErrors();
    isOpen.value = true;
};

const submit = () => {
    form.post(tags.store.url(), {
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
        <DialogContent class="sm:max-w-[425px] bg-zinc-950 border-zinc-800 text-zinc-100">
            <DialogHeader>
                <DialogTitle>Создание тега</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="name">Название</Label>
                    <Input id="name" v-model="form.name" type="text" placeholder="Например: VIP" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="color">Цвет</Label>
                    <div class="flex items-center gap-2">
                        <input
                            id="color"
                            v-model="form.color"
                            type="color"
                            class="w-12 h-10 rounded border border-zinc-700 cursor-pointer"
                        />
                        <Input v-model="form.color" type="text" placeholder="#3b82f6" class="flex-1" />
                    </div>
                    <InputError :message="form.errors.color" />
                </div>

                <DialogFooter class="pt-4 border-t border-zinc-800 gap-2 sm:gap-0">
                    <Button type="button" variant="outline" @click="isOpen = false">Отмена</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Создание...' : 'Создать' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
