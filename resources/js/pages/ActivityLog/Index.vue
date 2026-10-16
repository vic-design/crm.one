<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { ArrowLeft, Clock, User, FileText, AlertCircle } from 'lucide-vue-next';
// import activityLog from '@/routes/management/activityLog';

const props = defineProps<{
    logs: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}>();

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/');
    }
};

// Форматирование даты
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Получение красивого названия типа лога
const getLogTypeLabel = (logName: string) => {
    const map: Record<string, string> = {
        'default': 'Система',
        'users': 'Пользователи',
        'profiles': 'Профили',
        'contacts': 'Контакты',
        'roles': 'Роли',
    };
    return map[logName] || logName;
};

// Цвет бейджа в зависимости от типа лога
const getLogTypeColor = (logName: string) => {
    const map: Record<string, string> = {
        'default': 'bg-zinc-700 text-zinc-200',
        'users': 'bg-blue-600/20 text-blue-300 border-blue-500/30',
        'profiles': 'bg-purple-600/20 text-purple-300 border-purple-500/30',
        'contacts': 'bg-green-600/20 text-green-300 border-green-500/30',
        'roles': 'bg-orange-600/20 text-orange-300 border-orange-500/30',
    };
    return map[logName] || 'bg-zinc-700 text-zinc-200';
};
</script>

<template>
    <Head title="Журнал действий" />
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Заголовок и кнопка назад -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" @click="goBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <h1 class="text-2xl font-bold text-white">Журнал действий</h1>
                </div>
                <!-- Здесь позже будет кнопка Экспорта (Шаг 4) -->
                <!-- <Button variant="outline" class="gap-2">
                    <Download class="h-4 w-4" /> Экспорт
                </Button> -->
            </div>

            <!-- Таблица логов -->
            <div class="bg-zinc-900/50 border border-zinc-800 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-zinc-400 uppercase bg-zinc-950/50 border-b border-zinc-800">
                            <tr>
                                <th class="px-6 py-3">Дата и время</th>
                                <th class="px-6 py-3">Пользователь</th>
                                <th class="px-6 py-3">Тип</th>
                                <th class="px-6 py-3">Действие</th>
                                <th class="px-6 py-3">Детали изменений</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-zinc-800/50 transition-colors">
                                <!-- Дата -->
                                <td class="px-6 py-4 whitespace-nowrap text-zinc-400">
                                    <div class="flex items-center gap-2">
                                        <Clock class="h-4 w-4" />
                                        {{ formatDate(log.created_at) }}
                                    </div>
                                </td>

                                <!-- Кто сделал -->
                                <td class="px-6 py-4 whitespace-nowrap text-white">
                                    <div class="flex items-center gap-2">
                                        <User class="h-4 w-4 text-zinc-500" />
                                        {{ log.causer?.name || 'Система' }}
                                    </div>
                                </td>

                                <!-- Тип -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                        :class="getLogTypeColor(log.log_name)"
                                    >
                                        {{ getLogTypeLabel(log.log_name) }}
                                    </span>
                                </td>

                                <!-- Описание действия -->
                                <td class="px-6 py-4 text-zinc-300 max-w-xs">
                                    {{ log.description }}
                                </td>

                                <!-- Детали (Было -> Стало) -->
                                <td class="px-6 py-4 text-xs font-mono max-w-md">
                                    <div v-if="log.properties && (log.properties.old || log.properties.new)" class="space-y-1">
                                        <div v-if="log.properties.old" class="flex items-start gap-2 text-red-400">
                                            <span class="mt-0.5">-</span>
                                            <span class="break-all">{{ JSON.stringify(log.properties.old) }}</span>
                                        </div>
                                        <div v-if="log.properties.new" class="flex items-start gap-2 text-green-400">
                                            <span class="mt-0.5">+</span>
                                            <span class="break-all">{{ JSON.stringify(log.properties.new) }}</span>
                                        </div>
                                    </div>
                                    <div v-else class="text-zinc-500 italic flex items-center gap-2">
                                        <AlertCircle class="h-3 w-3" />
                                        Нет детальных данных
                                    </div>
                                </td>
                            </tr>

                            <!-- Пустое состояние -->
                            <tr v-if="logs.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-zinc-500">
                                    <FileText class="h-12 w-12 mx-auto mb-3 opacity-50" />
                                    <p>Журнал действий пуст</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <div v-if="logs.last_page > 1" class="px-6 py-4 border-t border-zinc-800 flex items-center justify-between">
                    <span class="text-sm text-zinc-400">
                        Страница {{ logs.current_page }} из {{ logs.last_page }}
                    </span>
                    <div class="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="logs.current_page === 1"
                            @click="router.get(logs.links.find((l: any) => l.label.includes('Previous'))?.url || '')"
                        >
                            Назад
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="logs.current_page === logs.last_page"
                            @click="router.get(logs.links.find((l: any) => l.label.includes('Next'))?.url || '')"
                        >
                            Вперед
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
