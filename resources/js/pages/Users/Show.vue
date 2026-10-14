<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { User } from '@/types';
import users from '@/routes/users';
import Button from '@/components/ui/button/Button.vue';
import FloatingBackButton from '@/components/FloatingBackButton.vue';
import { Pencil, ArrowLeft, Mail, Calendar, Shield, Tag } from 'lucide-vue-next';

const props = defineProps<{
    user: User;
}>();

const breadcrumbs = [
    { title: 'Пользователи', href: users.index.url() },
    { title: props.user.name, href: users.show.url({ user: props.user.id }) }
];

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(users.index.url());
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Пользователь: ${user.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Кнопка назад вверху -->
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="goBack">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <h1 class="text-2xl font-bold text-white">Профиль пользователя</h1>
            </div>

            <!-- Карточка пользователя -->
            <div class="bg-zinc-900/50 border border-zinc-800 rounded-lg p-6">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <!-- Аватар -->
                    <img
                        v-if="user.avatar_url"
                        :src="user.avatar_url"
                        class="w-24 h-24 rounded-full object-cover border-2 border-zinc-700"
                    />
                    <div v-else class="w-24 h-24 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-500 text-3xl font-bold border-2 border-zinc-700">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </div>

                    <!-- Информация -->
                    <div class="flex-1 space-y-2">
                        <h2 class="text-2xl font-bold text-white">{{ user.name }}</h2>
                        <div class="flex items-center gap-2 text-zinc-400">
                            <Mail class="h-4 w-4" />
                            <span>{{ user.email }}</span>
                            <span v-if="user.email_verified_at" class="text-green-500 text-xs">✓ подтверждён</span>
                        </div>
                        <div class="flex items-center gap-2 text-zinc-400 text-sm">
                            <Calendar class="h-4 w-4" />
                            <span>Создан: {{ formatDate(user.created_at) }}</span>
                        </div>
                    </div>

                    <!-- Кнопка редактирования -->
                    <Link :href="users.edit.url({ user: user.id })">
                        <Button variant="outline" class="gap-2">
                            <Pencil class="h-4 w-4" />
                            Редактировать
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Роли и теги -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Роли -->
                <div class="bg-zinc-900/50 border border-zinc-800 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <Shield class="h-5 w-5" />
                        Роли
                        <span class="text-sm font-normal text-zinc-400">({{ user.roles?.length || 0 }})</span>
                    </h3>
                    <div v-if="user.roles?.length" class="flex flex-wrap gap-2">
                        <span
                            v-for="role in user.roles"
                            :key="role.id"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-600/20 text-blue-300 border border-blue-500/30"
                        >
                            {{ role.name }}
                        </span>
                    </div>
                    <p v-else class="text-zinc-500 text-sm">Нет ролей</p>
                </div>

                <!-- Теги -->
                <div class="bg-zinc-900/50 border border-zinc-800 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <Tag class="h-5 w-5" />
                        Теги
                        <span class="text-sm font-normal text-zinc-400">({{ user.tags?.length || 0 }})</span>
                    </h3>
                    <div v-if="user.tags?.length" class="flex flex-wrap gap-2">
                        <Link
                            v-for="tag in user.tags"
                            :key="tag.id"
                            :href="`/tags/${tag.id}`"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border hover:opacity-80 transition-opacity"
                            :style="{
                                backgroundColor: tag.color + '20',
                                borderColor: tag.color + '50',
                                color: tag.color
                            }"
                        >
                            {{ tag.name }}
                        </Link>
                    </div>
                    <p v-else class="text-zinc-500 text-sm">Нет тегов</p>
                </div>
            </div>

            <!-- Детальная информация -->
            <div class="bg-zinc-900/50 border border-zinc-800 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-white mb-4">Детали</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-zinc-800">
                        <dt class="text-zinc-400">ID</dt>
                        <dd class="text-white font-mono">{{ user.id }}</dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-zinc-800">
                        <dt class="text-zinc-400">Email</dt>
                        <dd class="text-white">{{ user.email }}</dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-zinc-800">
                        <dt class="text-zinc-400">Email подтверждён</dt>
                        <dd class="text-white">
                            <span v-if="user.email_verified_at" class="text-green-500">Да</span>
                            <span v-else class="text-red-500">Нет</span>
                        </dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-zinc-800">
                        <dt class="text-zinc-400">Создан</dt>
                        <dd class="text-white">{{ formatDate(user.created_at) }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-zinc-400">Обновлён</dt>
                        <dd class="text-white">{{ formatDate(user.updated_at) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Плавающая кнопка назад -->
        <FloatingBackButton :fallback-url="users.index.url()" />
    </AppLayout>
</template>
