<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';
import TagSelector from '@/components/TagSelector.vue';
import users from '@/routes/users';
import { Contact } from '@/types';

const props = defineProps<{
    availableRoles: { id: number; name: string }[];
}>();

const isOpen = ref(false);

const contactTypes = [
    { value: 'phone', label: 'Телефон', needsDescription: false, placeholder: '+7 (999) 000-00-00' },
    { value: 'telegram', label: 'Telegram', needsDescription: false, placeholder: '@username' },
    { value: 'whatsapp', label: 'WhatsApp', needsDescription: false, placeholder: '+7 (999) 000-00-00' },
    { value: 'skype', label: 'Skype', needsDescription: false, placeholder: 'username' },
    { value: 'email', label: 'Email', needsDescription: false, placeholder: 'email@example.com' },
    { value: 'other', label: 'Другое', needsDescription: true, placeholder: 'Значение' },
];

const form = useForm({
    name: '',
    email: '',
    avatar: null as File | null,
    roles: [] as string[],
    tags: [] as number[],
    contacts: [] as Contact[],
});

const avatarPreview = computed(() => {
    return form.avatar ? URL.createObjectURL(form.avatar) : null;
});

const openDialog = () => {
    form.reset();
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
    form.post(users.store().url, {
        forceFormData: true,
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
        <DialogContent class="sm:max-w-[600px] bg-zinc-950 border-zinc-800 text-zinc-100 max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Создание пользователя</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4 py-4">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider">Основное</h3>
                    <div class="space-y-2">
                        <Label>Аватар</Label>
                        <div class="flex items-center gap-4">
                            <img v-if="avatarPreview" :src="avatarPreview" class="w-16 h-16 rounded-full object-cover border border-zinc-700" />
                            <div v-else class="w-16 h-16 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-500 border border-zinc-700">
                                <span class="text-xs">Нет фото</span>
                            </div>
                            <input type="file" @change="handleFileChange" accept="image/*" class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-zinc-800 file:text-zinc-200 hover:file:bg-zinc-700" />
                        </div>
                        <InputError :message="form.errors.avatar" />
                    </div>

                    <div class="space-y-2">
                        <Label for="name">Имя</Label>
                        <Input id="name" v-model="form.name" type="text" placeholder="Полное имя" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" placeholder="example@mail.com" />
                        <InputError :message="form.errors.email" />
                    </div>
                </div>

                <div class="space-y-4 pt-4 border-t border-zinc-800">
                    <h3 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider">Контакты и должность</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="position">Должность</Label>
                            <Input id="position" v-model="form.position" type="text" />
                        </div>
                        <div class="space-y-2">
                            <Label for="department">Отдел</Label>
                            <Input id="department" v-model="form.department" type="text" />
                        </div>
                        <div class="space-y-2">
                            <Label for="phone">Телефон</Label>
                            <Input id="phone" v-model="form.phone" type="text" placeholder="+7 (999) 000-00-00" />
                        </div>
                        <div class="space-y-2">
                            <Label for="telegram">Telegram</Label>
                            <Input id="telegram" v-model="form.telegram" type="text" placeholder="@username" />
                        </div>
                        <div class="space-y-2">
                            <Label for="whatsapp">WhatsApp</Label>
                            <Input id="whatsapp" v-model="form.whatsapp" type="text" placeholder="+7..." />
                        </div>
                        <div class="space-y-2">
                            <Label for="skype">Skype</Label>
                            <Input id="skype" v-model="form.skype" type="text" />
                        </div>
                    </div>
                </div>

                <!-- Секция: Комментарии -->
                <div class="space-y-2 pt-4 border-t border-zinc-800">
                    <Label for="bio">Комментарии / О себе</Label>
                    <textarea
                        id="bio"
                        v-model="form.bio"
                        rows="4"
                        class="w-full rounded-md border border-zinc-800 bg-zinc-900 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-700"
                        placeholder="Дополнительная информация о пользователе..."
                    ></textarea>
                    <InputError :message="form.errors.bio" />
                </div>

                <div class="space-y-2" v-if="availableRoles.length > 0">
                    <Label>Роли</Label>
                    <div class="grid grid-cols-1 gap-2 max-h-40 overflow-y-auto p-2 border border-zinc-800 rounded-md bg-zinc-900/50">
                        <label v-for="role in availableRoles" :key="role.id" class="flex items-center space-x-2 text-sm cursor-pointer text-zinc-300 hover:text-zinc-100">
                            <input type="checkbox" :value="role.name" v-model="form.roles" class="rounded bg-zinc-900 border-zinc-800 text-blue-600 focus:ring-blue-600" />
                            <span class="capitalize">{{ role.name }}</span>
                        </label>
                    </div>
                    <InputError :message="form.errors.roles" />
                </div>

                <div class="space-y-2">
                    <Label>Теги</Label>
                    <TagSelector v-model="form.tags" />
                    <InputError :message="form.errors.tags" />
                </div>

                <p class="text-xs text-zinc-500 mt-2">Пароль будет сгенерирован автоматически и показан в уведомлении.</p>

                <DialogFooter class="pt-4 border-t border-zinc-800 justify-between sm:justify-between">
                    <Button type="button" variant="outline" @click="isOpen = false">Отмена</Button>
                    <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Создание...' : 'Создать' }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
