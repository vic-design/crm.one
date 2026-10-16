<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import ExportButton from '@/components/ExportButton.vue';
import { ArrowLeft, Clock, User, FileText, AlertCircle, Eye } from 'lucide-vue-next';
import { formatDate, getLogTypeLabel, getLogTypeColor, getLogChanges, formatLogValue } from '@/lib/utils';
import LogDetailDialog from './Partials/LogDetailDialog.vue';
import { DateRange } from '@/types/activity-log.js';
import DateRangeFilter from '@/components/Filters/DateRangeFilter.vue';
import LogTypeFilter from '@/components/Filters/LogTypeFilter.vue';

const props = defineProps<{
    logs: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    selectedLog: any;
    filters?: {
        from: string | null;
        to: string | null;
        type: string | null;
    };
    availableTypes: string[];
}>();

const isDialogOpen = ref(false);

const dateRange = ref<DateRange>({
    from: props.filters?.from ?? '',
    to: props.filters?.to ?? '',
});

const logType = ref<string>(props.filters?.type ?? '');

const updateUrl = () => {
    const url = new URL(window.location.href);

    if (dateRange.value.from) url.searchParams.set('from', dateRange.value.from);
    else url.searchParams.delete('from');

    if (dateRange.value.to) url.searchParams.set('to', dateRange.value.to);
    else url.searchParams.delete('to');

    if (logType.value) url.searchParams.set('type', logType.value);
    else url.searchParams.delete('type');

    url.searchParams.delete('page');

    router.get(url.pathname + url.search, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(dateRange, updateUrl, { deep: true });
watch(logType, updateUrl);

const goBack = () => {
    if (window.history.length > 1) window.history.back();
    else router.visit('/');
};

const openLogDetail = (id: number) => {
    const url = new URL(window.location.href);
    url.searchParams.set('log_id', id.toString());
    router.get(url.pathname + url.search, {}, { preserveState: true, preserveScroll: true });
};

const closeLogDetail = () => {
    const url = new URL(window.location.href);
    url.searchParams.delete('log_id');
    router.get(url.pathname + url.search, {}, { preserveState: true, preserveScroll: true });
};

watch(() => props.selectedLog, (newLog) => {
    isDialogOpen.value = !!newLog;
}, { immediate: true });
</script>

<template>
    <Head title="Журнал действий" />
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Заголовок + фильтры + экспорт -->
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" @click="goBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <h1 class="text-2xl font-bold text-white">Журнал действий</h1>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <div v-if="availableTypes.length > 0" class="flex flex-col sm:flex-row gap-3">
                        <LogTypeFilter
                            v-model="logType"
                            :options="availableTypes"
                            label="Тип:"
                        />
                        <DateRangeFilter
                            v-model="dateRange"
                            label="Период:"
                        />
                    </div>

                    <ExportButton :filters="filters ?? { from: null, to: null, type: null }" />
                </div>
            </div>

            <!-- Таблица логов -->
            <div class="bg-zinc-900/50 border border-zinc-800 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-zinc-400 uppercase bg-zinc-950/50 border-b border-zinc-800">
                            <tr>
                                <th class="px-6 py-3 w-16"></th>
                                <th class="px-6 py-3">Дата и время</th>
                                <th class="px-6 py-3">Пользователь</th>
                                <th class="px-6 py-3">Тип</th>
                                <th class="px-6 py-3">Действие</th>
                                <th class="px-6 py-3">Детали изменений</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-zinc-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="openLogDetail(log.id)">
                                        <Eye class="h-4 w-4 text-zinc-400 hover:text-white" />
                                    </Button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-zinc-400">
                                    <div class="flex items-center gap-2">
                                        <Clock class="h-4 w-4" />
                                        {{ formatDate(log.created_at) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-white">
                                    <div class="flex items-center gap-2">
                                        <User class="h-4 w-4 text-zinc-500" />
                                        {{ log.causer?.name || 'Система' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                        :class="getLogTypeColor(log.log_name)"
                                    >
                                        {{ getLogTypeLabel(log.log_name) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-zinc-300 max-w-xs truncate" :title="log.description">
                                    {{ log.description }}
                                </td>
                                <td class="px-6 py-4 text-xs font-mono max-w-md">
                                    <div v-if="getLogChanges(log).length" class="space-y-1">
                                        <template v-for="change in getLogChanges(log)" :key="change.key">
                                            <div v-if="change.oldValue !== undefined" class="flex items-start gap-2 text-red-400">
                                                <span class="mt-0.5 select-none">-</span>
                                                <span class="truncate max-w-[250px]" :title="`${change.key}: ${formatLogValue(change.oldValue)}`">
                                                    {{ change.key }}: {{ formatLogValue(change.oldValue) }}
                                                </span>
                                            </div>
                                            <div v-if="change.newValue !== undefined" class="flex items-start gap-2 text-green-400">
                                                <span class="mt-0.5 select-none">+</span>
                                                <span class="truncate max-w-[250px]" :title="`${change.key}: ${formatLogValue(change.newValue)}`">
                                                    {{ change.key }}: {{ formatLogValue(change.newValue) }}
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                    <div v-else class="text-zinc-500 italic flex items-center gap-2">
                                        <AlertCircle class="h-3 w-3" />
                                        Нет детальных данных
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                                    <FileText class="h-12 w-12 mx-auto mb-3 opacity-50" />
                                    <p>Записи не найдены</p>
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

            <!-- Диалог деталей -->
            <LogDetailDialog
                v-if="selectedLog"
                :log="selectedLog"
                :open="isDialogOpen"
                @update:open="(val) => { if (!val) closeLogDetail() }"
            />
        </div>
    </AppLayout>
</template>
