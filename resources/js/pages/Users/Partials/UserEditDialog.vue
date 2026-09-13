<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';
import users from '@/routes/users';

interface UserEditPayload {
    id: number;
    name: string;
    email: string;
}

const isOpen = ref(false);
const currentUserId = ref<number | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
});

const openDialog = (user: UserEditPayload) => {
    currentUserId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = ''; // Не подгружаем пароль по соображениям безопасности
    form.clearErrors();
    isOpen.value = true;
};

const submit = () => {
    if (!currentUserId.value) return;
    form.put(users.update(currentUserId.value).url, {
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
            <DialogTitle>Редактирование пользователя</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submit" class="space-y-4 py-4">
            <div class="space-y-2">
            <Label for="name">Имя</Label>
            <Input id="name" v-model="form.name" type="text" />
            <InputError :message="form.errors.name" />
            </div>
            <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input id="email" v-model="form.email" type="email" />
            <InputError :message="form.errors.email" />
            </div>
            <div class="space-y-2">
            <Label for="password">Новый пароль</Label>
            <Input id="password" v-model="form.password" type="password" placeholder="Оставьте пустым, чтобы не менять" />
            <InputError :message="form.errors.password" />
            </div>
            <DialogFooter class="pt-4 border-t border-zinc-800 gap-2 sm:gap-0">
            <Button type="button" variant="outline" @click="isOpen = false">Отмена</Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Сохранение...' : 'Сохранить' }}
            </Button>
            </DialogFooter>
        </form>
        </DialogContent>
    </Dialog>
</template>
