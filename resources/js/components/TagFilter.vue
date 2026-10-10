<script setup lang="ts">
import { computed } from 'vue';
import type { Tag } from '@/types';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import Button from '@/components/ui/button/Button.vue';
import { X, Tag as TagIcon } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
    modelValue: number[];
    availableTags: Tag[];
    label?: string;
}>(), {
    label: 'Теги'
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: number[]): void;
}>();

const toggleTag = (tagId: number) => {
    const current = [...props.modelValue];
    const index = current.indexOf(tagId);

    if (index === -1) {
        current.push(tagId);
    } else {
        current.splice(index, 1);
    }

    emit('update:modelValue', current);
};

const isSelected = (tagId: number) => props.modelValue.includes(tagId);

const resetFilter = () => {
    emit('update:modelValue', []);
};

const hasSelection = computed(() => props.modelValue.length > 0);
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" class="gap-2">
                <TagIcon class="h-4 w-4" />
                {{ label }}
                <span
                    v-if="hasSelection"
                    class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-xs"
                >
                    {{ modelValue.length }}
                </span>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-64 p-3 bg-zinc-950 border-zinc-800" align="start">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-zinc-300">Фильтр по {{ label.toLowerCase() }}</span>
                <Button
                    v-if="hasSelection"
                    variant="ghost"
                    size="sm"
                    class="h-6 px-2 text-xs"
                    @click="resetFilter"
                >
                    <X class="h-3 w-3 mr-1" /> Сбросить
                </Button>
            </div>
            <div class="space-y-1 max-h-60 overflow-y-auto">
                <div
                    v-for="tag in availableTags"
                    :key="tag.id"
                    class="flex items-center gap-2 p-2 rounded hover:bg-zinc-900 cursor-pointer text-sm"
                    @click="toggleTag(tag.id)"
                >
                    <input
                        type="checkbox"
                        :checked="isSelected(tag.id)"
                        class="pointer-events-none rounded bg-zinc-900 border-zinc-700 text-blue-600 focus:ring-blue-600"
                    />
                    <div
                        class="w-3 h-3 rounded"
                        :style="{ backgroundColor: tag.color }"
                    ></div>
                    <span class="text-zinc-300">{{ tag.name }}</span>
                </div>
                <div v-if="!availableTags.length" class="text-center text-zinc-500 text-xs py-2">
                    {{ label }} не найдены
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
