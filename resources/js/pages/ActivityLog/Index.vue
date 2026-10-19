<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, DateRange } from '@/types';
import Button from '@/components/ui/button/Button.vue';
import DateRangeFilter from '@/components/filters/DateRangeFilter.vue';
import LogTypeFilter from '@/components/filters/LogTypeFilter.vue';
import ExportButton from '@/components/ExportButton.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Eye } from 'lucide-vue-next';
import { formatDate, getLogTypeLabel, getLogTypeColor, getLogChanges, formatLogValue } from '@/lib/utils';
import LogDetailDialog from './Partials/LogDetailDialog.vue';
import Pagination from '@/components/pagination/Index.vue';

const props = defineProps<{
    logs: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    selectedLog: any;
    filters?: { from: string | null; to: string | null; type: string | null; per_page?: number };
    availableTypes: string[];
    perPageOptions?: number[];
}>();

const breadCrumbs: BreadcrumbItem[] = [
    { title: 'Журнал действий', href: '#' }
];

const isDialogOpen = ref(false);
const dateRange = ref<DateRange>({ from: props.filters?.from ?? '', to: props.filters?.to ?? '' });
const logType = ref<string>(props.filters?.type ?? '');
const currentPerPage = ref(props.filters?.per_page || 20);

const updateUrl = () => {
    const url = new URL(window.location.href);
    if (dateRange.value.from) url.searchParams.set('from', dateRange.value.from); else url.searchParams.delete('from');
    if (dateRange.value.to) url.searchParams.set('to', dateRange.value.to); else url.searchParams.delete('to');
    if (logType.value) url.searchParams.set('type', logType.value); else url.searchParams.delete('type');
    url.searchParams.delete('page');

    router.get(url.pathname + url.search, {}, { preserveState: true, preserveScroll: true, replace: true });
};

watch(dateRange, updateUrl, { deep: true });
watch(logType, updateUrl);

watch(currentPerPage, (newVal) => {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', newVal.toString());
    url.searchParams.delete('page');
    router.get(url.pathname + url.search, {}, { preserveState: true, preserveScroll: true, replace: true });
});

watch(() => props.filters?.per_page, (newVal) => {
    if (newVal && newVal !== currentPerPage.value) {
        currentPerPage.value = newVal;
    }
});

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
    <AppLayout :breadcrumbs="breadCrumbs">
        <div class="p-6 space-y-6">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" @click="goBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <h1 class="text-2xl font-bold text-white">Журнал действий</h1>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <div v-if="availableTypes.length > 0" class="flex flex-col sm:flex-row gap-3">
                        <LogTypeFilter v-model="logType" :options="availableTypes" label="Тип:" />
                        <DateRangeFilter v-model="dateRange" label="Период:" />
                    </div>
                    <ExportButton :filters="filters" />
                </div>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[60px]"></TableHead>
                        <TableHead>Дата и время</TableHead>
                        <TableHead>Пользователь</TableHead>
                        <TableHead>Тип</TableHead>
                        <TableHead>Действие</TableHead>
                        <TableHead class="text-right">Детали изменений</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="logs.data.length > 0">
                        <TableRow v-for="log in logs.data" :key="log.id">
                            <TableCell>
                                <Button variant="ghost" size="icon" class="h-8 w-8" @click="openLogDetail(log.id)">
                                    <Eye class="h-4 w-4 text-zinc-400 hover:text-white" />
                                </Button>
                            </TableCell>
                            <TableCell class="text-zinc-400">{{ formatDate(log.created_at) }}</TableCell>
                            <TableCell class="text-white">{{ log.causer?.name || 'Система' }}</TableCell>
                            <TableCell>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border" :class="getLogTypeColor(log.log_name)">
                                    {{ getLogTypeLabel(log.log_name) }}
                                </span>
                            </TableCell>
                            <TableCell class="text-zinc-300 max-w-xs truncate" :title="log.description">{{ log.description }}</TableCell>
                            <TableCell class="text-right">
                                <div v-if="getLogChanges(log).length" class="space-y-1">
                                    <template v-for="change in getLogChanges(log)" :key="change.key">
                                        <div v-if="change.oldValue !== undefined" class="flex items-start gap-2 text-red-400 text-xs justify-end">
                                            <span class="truncate max-w-[200px] text-right">{{ change.key }}: {{ formatLogValue(change.oldValue) }}</span>
                                            <span class="mt-0.5 select-none">-</span>
                                        </div>
                                        <div v-if="change.newValue !== undefined" class="flex items-start gap-2 text-green-400 text-xs justify-end">
                                            <span class="truncate max-w-[200px] text-right">{{ change.key }}: {{ formatLogValue(change.newValue) }}</span>
                                            <span class="mt-0.5 select-none">+</span>
                                        </div>
                                    </template>
                                </div>
                                <div v-else class="text-zinc-500 text-xs italic">Нет данных</div>
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else>
                        <TableCell colspan="6" class="h-24 text-center text-zinc-500">Записи не найдены</TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <Pagination
                v-model:per-page="currentPerPage"
                :total="logs.total"
                :current-page="logs.current_page"
                :links="logs.links"
                :prev-page-url="logs.prev_page_url"
                :next-page-url="logs.next_page_url"
                :per-page-options="perPageOptions || [20, 30, 50]"
            />

            <LogDetailDialog
                v-if="selectedLog"
                :log="selectedLog"
                :open="isDialogOpen"
                @update:open="(val) => { if (!val) closeLogDetail() }"
            />
        </div>
    </AppLayout>
</template>
