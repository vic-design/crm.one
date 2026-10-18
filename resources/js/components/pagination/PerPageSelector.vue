<script setup lang="ts">
const props = defineProps<{
    modelValue: number;
    total: number;
    options: number[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number): void;
}>();
</script>

<template>
    <div class="flex items-center gap-2 sm:justify-start justify-center">
        <span class="text-sm text-zinc-400">Показывать по:</span>
        <select
            :value="modelValue"
            @change="emit('update:modelValue', Number(($event.target as HTMLSelectElement).value))"
            class="h-9 w-20 rounded-md border border-zinc-800 bg-zinc-950 px-3 text-sm text-zinc-200 outline-none focus:ring-1 focus:ring-zinc-700 hover:bg-zinc-900 transition-colors cursor-pointer"
        >
            <option v-for="option in options" :key="option" :value="option">
                {{ option }}
            </option>
        </select>
        <span class="text-sm text-zinc-500">
            из {{ total }}
        </span>
    </div>
</template>
