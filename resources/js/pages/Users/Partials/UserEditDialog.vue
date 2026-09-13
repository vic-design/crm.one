<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';
import users from '@/routes/users';
import type { User } from '@/types';

const isOpen = ref(false);
const currentUserId = ref<number | null>(null);
const currentUser = ref<User | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    avatar: null as File | null,
});

const avatarPreview = computed(() => {
    if (form.avatar) {
        return URL.createObjectURL(form.avatar);
    }
    return currentUser.value?.avatar_url ?? null;
});

const openDialog = (user: User) => {
    currentUserId.value = user.id;
    currentUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.avatar = null;
    form.clearErrors();
    isOpen.value = true;
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.avatar = target.files[0];
    }
};

const submit = () => {
    if (!currentUserId.value) return;
    form.put(users.update(currentUserId.value).url, {
        forceFormData: true,
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
                    <Label>Аватар</Label>
                    <div class="flex items-center gap-4">
                        <img
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            class="w-16 h-16 rounded-full object-cover border border-zinc-700"
                        />
                        <div v-else class="w-16 h-16 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-500 border border-zinc-700">
                            <span class="text-xs">Нет фото</span>
                        </div>
                        <input
                            type="file"
                            @change="handleFileChange"
                            accept="image/*"
                            class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-zinc-800 file:text-zinc-200 hover:file:bg-zinc-700"
                        />
                    </div>
                    <InputError :message="form.errors.avatar" />
                </div>

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
