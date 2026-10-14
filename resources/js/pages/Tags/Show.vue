<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Tag, User } from '@/types';
import tags from '@/routes/tags';
import { ArrowLeft } from 'lucide-vue-next';
import { useBack } from '@/composables/useBack';
import FloatingBackButton from '@/components/FloatingBackButton.vue';

const props = defineProps<{
    tag: Tag & {
        users: User[];
        clients?: any[];  // когда создадите
        tasks?: any[];  // когда создадите
    };
}>();

const breadcrumbs = [
    { title: 'Теги', href: tags.index.url() },
    { title: props.tag.name, href: tags.show.url({ tag: props.tag.id }) }
];

const { goBack } = useBack(tags.index.url());
</script>

<template>
    <Head :title="`Тег: ${tag.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">

            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="goBack">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <h1 class="text-2xl font-bold text-white">Тег: {{ tag.name }}</h1>
            </div>

            <!-- Заголовок тега -->
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-lg border"
                    :style="{
                        backgroundColor: tag.color + '20',
                        borderColor: tag.color + '50'
                    }"
                ></div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ tag.name }}</h1>
                    <p class="text-sm text-zinc-400">Slug: {{ tag.slug }}</p>
                </div>
            </div>

            <!-- Пользователи -->
            <div class="bg-zinc-900/50 border border-zinc-800 rounded-lg p-4">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    👥 Пользователи
                    <span class="text-sm font-normal text-zinc-400">({{ tag.users?.length || 0 }})</span>
                </h2>
                <div v-if="tag.users?.length" class="space-y-2">
                    <div
                        v-for="user in tag.users"
                        :key="user.id"
                        class="flex items-center gap-3 p-3 bg-zinc-900 rounded-lg hover:bg-zinc-800 transition-colors"
                    >
                        <img
                            v-if="user.avatar_url"
                            :src="user.avatar_url"
                            class="w-10 h-10 rounded-full object-cover"
                        />
                        <div v-else class="w-10 h-10 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-500">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-white">{{ user.name }}</p>
                            <p class="text-sm text-zinc-400">{{ user.email }}</p>
                        </div>
                        <Link
                            :href="`/users/${user.id}`"
                            class="text-blue-400 hover:text-blue-300 text-sm"
                        >
                            Открыть →
                        </Link>
                    </div>
                </div>
                <p v-else class="text-zinc-500 text-sm">Нет привязанных пользователей</p>
            </div>

        </div>

        <FloatingBackButton :fallback-url="tags.index.url()" :show-after="0" />

    </AppLayout>
</template>
