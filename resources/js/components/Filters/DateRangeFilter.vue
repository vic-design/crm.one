<script setup lang="ts">
import { computed } from 'vue';
import { Calendar, X } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import type { DateRange } from '@/types/activity-log';

const props = defineProps<{
    modelValue: DateRange;
    label?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: DateRange): void;
}>();

const from = computed({
    get: () => props.modelValue.from,
    set: (val) => emit('update:modelValue', { ...props.modelValue, from: val }),
});

const to = computed({
    get: () => props.modelValue.to,
    set: (val) => emit('update:modelValue', { ...props.modelValue, to: val }),
});

const hasValue = computed(() => !!props.modelValue.from || !!props.modelValue.to);

const clear = () => {
    emit('update:modelValue', { from: '', to: '' });
};
</script>

<template>
    <div class="flex items-center gap-2">
        <label v-if="label" class="text-sm text-zinc-400 whitespace-nowrap">{{ label }}</label>

        <div class="flex items-center gap-2 bg-zinc-900/50 border border-zinc-800 rounded-md px-3 py-1.5">
            <Calendar class="h-4 w-4 text-zinc-500" />

            <Input
                type="date"
                :value="from"
                @input="from = ($event.target as HTMLInputElement).value"
                class="bg-transparent text-sm text-zinc-100 outline-none [color-scheme:dark]"
            />

            <span class="text-zinc-600">—</span>

            <Input
                type="date"
                :value="to"
                @input="to = ($event.target as HTMLInputElement).value"
                class="bg-transparent text-sm text-zinc-100 outline-none [color-scheme:dark]"
            />

            <Button
                v-if="hasValue"
                variant="ghost"
                size="icon"
                class="h-6 w-6 text-zinc-500 hover:text-zinc-100"
                @click="clear"
            >
                <X class="h-3 w-3" />
            </Button>
        </div>
    </div>
</template>
