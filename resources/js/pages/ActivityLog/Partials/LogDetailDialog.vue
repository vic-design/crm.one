<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { formatDate, getLogTypeLabel, getLogTypeColor, getLogChanges, formatLogValue } from '@/lib/utils';
import { User, Calendar, FileText } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    open: boolean;
    log: any;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const formatValue = (value: any) => {
    if (value === null) return '<null>';
    if (typeof value === 'object') return JSON.stringify(value, null, 2);
    return String(value);
};


const changes = computed(() => getLogChanges(props.log));
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[600px] bg-zinc-950 border-zinc-800 text-zinc-100 max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <FileText class="h-5 w-5 text-zinc-400" />
                    Детали действия
                </DialogTitle>
                <DialogDescription class="text-zinc-400">
                    Подробная информация о выбранном событии в системе.
                </DialogDescription>
            </DialogHeader>

            <div v-if="log" class="space-y-6 py-4">
                <!-- Основная информация -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="space-y-1">
                        <div class="text-zinc-500 flex items-center gap-2">
                            <Calendar class="h-4 w-4" /> Дата и время
                        </div>
                        <div class="text-white font-medium">{{ formatDate(log.created_at) }}</div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-zinc-500 flex items-center gap-2">
                            <User class="h-4 w-4" /> Инициатор
                        </div>
                        <div class="text-white font-medium">{{ log.causer?.name || 'Система' }}</div>
                    </div>
                </div>

                <!-- Тип и описание -->
                <div class="space-y-2 p-4 bg-zinc-900/50 rounded-lg border border-zinc-800">
                    <div class="flex items-center gap-2 mb-2">
                        <Badge :class="getLogTypeColor(log.log_name)">
                            {{ getLogTypeLabel(log.log_name) }}
                        </Badge>
                    </div>
                    <p class="text-zinc-200">{{ log.description }}</p>
                    <p v-if="log.subject_type" class="text-xs text-zinc-500 font-mono mt-2">
                        Объект: {{ log.subject_type }} #{{ log.subject_id }}
                    </p>
                </div>

                <!-- Изменения (Diff) -->
                <div v-if="changes.length" class="space-y-3">
                    <h4 class="text-sm font-semibold text-zinc-300 uppercase tracking-wider">Измененные данные</h4>

                    <div class="space-y-2">
                        <div
                            v-for="change in changes"
                            :key="change.key"
                            class="p-3 bg-zinc-900 rounded-md border border-zinc-800"
                        >
                            <div class="text-xs text-zinc-500 uppercase mb-2 font-semibold">{{ change.key }}</div>

                            <div v-if="change.oldValue !== undefined" class="flex items-start gap-2 text-red-400 text-sm font-mono">
                                <span class="mt-0.5 select-none">-</span>
                                <span class="break-all">{{ formatLogValue(change.oldValue) }}</span>
                            </div>
                            <div v-if="change.newValue !== undefined" class="flex items-start gap-2 text-green-400 text-sm font-mono">
                                <span class="mt-0.5 select-none">+</span>
                                <span class="break-all">{{ formatLogValue(change.newValue) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8 text-zinc-500 text-sm border border-dashed border-zinc-800 rounded-lg">
                    Детальные данные об изменениях отсутствуют
                </div>            </div>
        </DialogContent>
    </Dialog>
</template>
