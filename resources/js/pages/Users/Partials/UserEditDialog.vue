<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import type { User, Role, Contact } from '@/types';
import { CONTACT_TYPES, createEmptyContact } from '@/types/contacts';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';
import TagSelector from '@/components/TagSelector.vue';
import users from '@/routes/users';
import axios from 'axios';
import { Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{ user: User | null }>();
const isOpen = ref(false);
const currentUserId = ref<number | null>(null);
const availableRoles = ref<Pick<Role, 'id' | 'name'>[]>([]);

const form = useForm({
    name: '',
    email: '',
    avatar: null as File | null,
    roles: [] as string[],
    tags: [] as number[],
    password: '',
    bio: '',
    contacts: [] as Contact[],
});

const avatarPreview = ref<string | null>(null);

const openDialog = async (user: User) => {
    currentUserId.value = user.id;

    if (availableRoles.value.length === 0) {
        const response = await axios.get('/api/roles');
        availableRoles.value = response.data;
    }

    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.roles = user.roles.map(r => r.name);
    form.tags = user.tags?.map(t => t.id) || [];
    form.bio = user.profile?.bio || '';

    form.contacts = user.profile?.contacts?.length
        ? user.profile.contacts.map(c => ({
            type: c.type,
            description: c.description ?? '',
            value: c.value,
        }))
        : [createEmptyContact()];

    avatarPreview.value = user.avatar_url || null;
    form.clearErrors();
    isOpen.value = true;
};

const addContact = () => {
    form.contacts.push(createEmptyContact());
};

const removeContact = (index: number) => {
    form.contacts.splice(index, 1);
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.avatar = target.files[0];
        avatarPreview.value = URL.createObjectURL(target.files[0]);
    }
};

const submit = () => {
    if (!currentUserId.value) return;

    form.put(users.update.url({ user: currentUserId.value }), {
        forceFormData: true,
        onSuccess: () => {
            isOpen.value = false;
            router.reload();
        },
    });
};

defineExpose({ openDialog });
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-[600px] bg-zinc-950 border-zinc-800 text-zinc-100 max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Редактирование пользователя</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4 py-4">
                <!-- Основное -->
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
                        <Input id="name" v-model="form.name" type="text" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Новый пароль (оставьте пустым, чтобы не менять)</Label>
                        <Input id="password" v-model="form.password" type="password" />
                        <InputError :message="form.errors.password" />
                    </div>
                </div>

                <!-- Контакты -->
                <div class="space-y-4 pt-4 border-t border-zinc-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider">Контакты</h3>
                        <Button type="button" variant="ghost" size="sm" class="h-8 gap-1" @click="addContact">
                            <Plus class="h-4 w-4" /> Добавить
                        </Button>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(contact, index) in form.contacts" :key="index" class="space-y-2">
                            <div class="flex gap-2 items-start">
                                <select
                                    v-model="contact.type"
                                    class="w-1/3 rounded-md border border-zinc-800 bg-zinc-900 px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-zinc-700"
                                >
                                    <option v-for="type in CONTACT_TYPES" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                                <Input
                                    v-model="contact.value"
                                    :placeholder="CONTACT_TYPES.find(t => t.value === contact.type)?.placeholder || 'Значение'"
                                    class="flex-1"
                                />
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="text-zinc-500 hover:text-red-500"
                                    @click="removeContact(index)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                            <div class="ml-[33.33%]">
                                <Input
                                    v-model="contact.description"
                                    :placeholder="CONTACT_TYPES.find(t => t.value === contact.type)?.needsDescription ? 'Обязательно: например, LinkedIn...' : 'Описание (необязательно)'"
                                    class="text-xs"
                                    :class="{
                                        'border-yellow-600 focus:ring-yellow-600': CONTACT_TYPES.find(t => t.value === contact.type)?.needsDescription && !contact.description
                                    }"
                                />
                                <InputError :message="form.errors[`contacts.${index}.description`]" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Роли и теги -->
                <div class="space-y-4 pt-4 border-t border-zinc-800">
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
                </div>

                <!-- Био -->
                <div class="space-y-2 pt-4 border-t border-zinc-800">
                    <Label for="bio">Комментарии / О себе</Label>
                    <textarea
                        id="bio"
                        v-model="form.bio"
                        rows="3"
                        class="w-full rounded-md border border-zinc-800 bg-zinc-900 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-700"
                    ></textarea>
                    <InputError :message="form.errors.bio" />
                </div>

                <DialogFooter class="pt-4 border-t border-zinc-800 justify-between sm:justify-between">
                    <Button type="button" variant="outline" @click="isOpen = false">Отмена</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Сохранение...' : 'Сохранить' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
