<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import ExportButton from '@/components/ExportButton.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationEllipsis,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { ArrowLeft, Eye } from 'lucide-vue-next';
import { formatDate, getLogTypeLabel, getLogTypeColor, getLogChanges, formatLogValue } from '@/lib/utils';
import LogDetailDialog from './Partials/LogDetailDialog.vue';
import { DateRange } from '@/types/activity-log.js';
import LogTypeFilter from '@/components/Filters/LogTypeFilter.vue';
import DateRangeFilter from '@/components/Filters/DateRangeFilter.vue';

const props = defineProps<{
    logs: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
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

const numericLinks = computed(() => props.logs.links.slice(1, -1));

const prevUrl = computed(() => props.logs.links.find((l: any) => l.label.includes('Previous'))?.url);
const nextUrl = computed(() => props.logs.links.find((l: any) => l.label.includes('Next'))?.url);
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

                    <ExportButton :filters="filters" />
                </div>
            </div>

            <!-- Таблица логов с использованием UI компонентов -->
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
                            <TableCell class="text-zinc-400">
                                {{ formatDate(log.created_at) }}
                            </TableCell>
                            <TableCell class="text-white">
                                {{ log.causer?.name || 'Система' }}
                            </TableCell>
                            <TableCell>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border"
                                    :class="getLogTypeColor(log.log_name)"
                                >
                                    {{ getLogTypeLabel(log.log_name) }}
                                </span>
                            </TableCell>
                            <TableCell class="text-zinc-300 max-w-xs truncate" :title="log.description">
                                {{ log.description }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div v-if="getLogChanges(log).length" class="space-y-1">
                                    <template v-for="change in getLogChanges(log)" :key="change.key">
                                        <div v-if="change.oldValue !== undefined" class="flex items-start gap-2 text-red-400 text-xs justify-end">
                                            <span class="truncate max-w-[200px] text-right">
                                                {{ change.key }}: {{ formatLogValue(change.oldValue) }}
                                            </span>
                                            <span class="mt-0.5 select-none">-</span>
                                        </div>
                                        <div v-if="change.newValue !== undefined" class="flex items-start gap-2 text-green-400 text-xs justify-end">
                                            <span class="truncate max-w-[200px] text-right">
                                                {{ change.key }}: {{ formatLogValue(change.newValue) }}
                                            </span>
                                            <span class="mt-0.5 select-none">+</span>
                                        </div>
                                    </template>
                                </div>
                                <div v-else class="text-zinc-500 text-xs italic">
                                    Нет данных
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else>
                        <TableCell colspan="6" class="h-24 text-center text-zinc-500">
                            Записи не найдены
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Пагинация с использованием UI компонента (исправленная) -->
            <div v-if="logs.last_page > 1" class="flex justify-center">
                <Pagination
                    v-slot="{ page }"
                    :items-per-page="logs.per_page"
                    :total="logs.total"
                    :default-page="1"
                    :page="logs.current_page"
                    class="flex justify-center"
                >
                    <PaginationContent>
                        <!-- Previous -->
                        <PaginationItem :value="logs.current_page - 1 || 1">
                            <Link v-if="prevUrl" :href="prevUrl" preserve-state preserve-scroll>
                                <PaginationPrevious />
                            </Link>
                            <PaginationPrevious v-else class="pointer-events-none opacity-50" />
                        </PaginationItem>

                        <!-- Numbers -->
                        <template v-for="(link, idx) in numericLinks" :key="idx">
                            <PaginationItem v-if="link.label === '...'" :value="idx">
                                <PaginationEllipsis />
                            </PaginationItem>
                            <PaginationItem v-else :value="Number(link.label)" :is-active="link.active">
                                <Link
                                    :href="link.url || '#'"
                                    preserve-state
                                    preserve-scroll
                                    class="inline-flex items-center justify-center rounded-md text-sm font-medium h-9 w-9 border transition-colors"
                                    :class="{
                                        'bg-blue-600 text-white border-blue-600': link.active,
                                        'bg-zinc-900 text-zinc-200 border-zinc-800 hover:bg-zinc-800': !link.active && link.url,
                                        'pointer-events-none opacity-30': !link.url
                                    }"
                                    v-html="link.label"
                                />
                            </PaginationItem>
                        </template>

                        <!-- Next -->
                        <PaginationItem :value="logs.current_page + 1">
                            <Link v-if="nextUrl" :href="nextUrl" preserve-state preserve-scroll>
                                <PaginationNext />
                            </Link>
                            <PaginationNext v-else class="pointer-events-none opacity-50" />
                        </PaginationItem>
                    </PaginationContent>
                </Pagination>
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
