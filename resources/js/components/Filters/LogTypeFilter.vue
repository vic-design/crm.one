<script setup lang="ts">
import { computed } from 'vue';
import { Filter, X } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import { getLogTypeLabel } from '@/lib/utils';

const props = defineProps<{
    modelValue: string;
    options: string[];
    label?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const hasValue = computed(() => !!props.modelValue);

const clear = () => {
    emit('update:modelValue', '');
};
</script>

<template>
    <div class="flex items-center gap-2">
        <label v-if="label" class="text-sm text-zinc-400 whitespace-nowrap">{{ label }}</label>

        <div class="relative">
            <select
                :value="modelValue"
                @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
                class="appearance-none bg-zinc-900/50 border border-zinc-800 rounded-md pl-9 pr-8 py-1.5 text-sm text-zinc-100 outline-none focus:ring-1 focus:ring-zinc-700 cursor-pointer min-w-[150px]"
            >
                <option value="" class="bg-zinc-900 text-zinc-400">Все типы</option>
                <option
                    v-for="opt in options"
                    :key="opt"
                    :value="opt"
                    class="bg-zinc-900 text-zinc-100"
                >
                    {{ getLogTypeLabel(opt) }}
                </option>
            </select>

            <Filter class="absolute left-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-500 pointer-events-none" />

            <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg class="h-4 w-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <Button
            v-if="hasValue"
            variant="ghost"
            size="icon"
            class="h-8 w-8 text-zinc-500 hover:text-zinc-100"
            @click="clear"
        >
            <X class="h-4 w-4" />
        </Button>
    </div>
</template>
