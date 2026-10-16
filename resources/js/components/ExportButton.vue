<script setup lang="ts">
import { ref } from 'vue';
import { Download, FileSpreadsheet, FileText, X } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';

const props = defineProps<{
    filters: {
        from?: string | null;
        to?: string | null;
        type?: string | null;
    };
}>();

const isOpen = ref(false);

const buildUrl = (format: string) => {
    const url = new URL(window.location.origin + '/management/activity-log/export/' + format);

    if (props.filters.from) url.searchParams.set('from', props.filters.from);
    if (props.filters.to) url.searchParams.set('to', props.filters.to);
    if (props.filters.type) url.searchParams.set('type', props.filters.type);

    return url.toString();
};

const exportFormat = (format: string) => {
    window.location.href = buildUrl(format);
    isOpen.value = false;
};
</script>

<template>
    <div class="relative">
        <Button
            variant="outline"
            class="gap-2"
            @click="isOpen = !isOpen"
        >
            <Download class="h-4 w-4" />
            Экспорт
        </Button>

        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-48 bg-zinc-900 border border-zinc-800 rounded-md shadow-lg z-50"
        >
            <div class="py-1">
                <button
                    @click="exportFormat('xlsx')"
                    class="w-full px-4 py-2 text-left text-sm text-zinc-100 hover:bg-zinc-800 flex items-center gap-2"
                >
                    <FileSpreadsheet class="h-4 w-4 text-green-500" />
                    Excel (XLSX)
                </button>
                <button
                    @click="exportFormat('csv')"
                    class="w-full px-4 py-2 text-left text-sm text-zinc-100 hover:bg-zinc-800 flex items-center gap-2"
                >
                    <FileText class="h-4 w-4 text-blue-500" />
                    CSV
                </button>
                <button
                    @click="exportFormat('pdf')"
                    class="w-full px-4 py-2 text-left text-sm text-zinc-100 hover:bg-zinc-800 flex items-center gap-2"
                >
                    <FileText class="h-4 w-4 text-red-500" />
                    PDF
                </button>
            </div>
        </div>

        <div v-if="isOpen" class="fixed inset-0 z-40" @click="isOpen = false"></div>
    </div>
</template>
