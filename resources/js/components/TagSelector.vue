<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import type { Tag } from '@/types';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import Button from '@/components/ui/button/Button.vue';
import { X, Plus } from 'lucide-vue-next';

const props = defineProps<{
    modelValue: number[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number[]): void;
}>();

const allTags = ref<Tag[]>([]);
const isOpen = ref(false);
const search = ref('');

onMounted(async () => {
    const response = await axios.get('/api/tags');
    allTags.value = response.data;
});

const filteredTags = computed(() => {
    if (!search.value) return allTags.value;
    return allTags.value.filter(tag =>
        tag.name.toLowerCase().includes(search.value.toLowerCase())
    );
});

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

const removeTag = (tagId: number) => {
    const current = props.modelValue.filter(id => id !== tagId);
    emit('update:modelValue', current);
};

const selectedTags = computed(() => {
    return allTags.value.filter(tag => props.modelValue.includes(tag.id));
});

import { computed } from 'vue';
</script>

<template>
    <div class="space-y-2">
        <div class="flex flex-wrap gap-2" v-if="selectedTags.length > 0">
            <div
                v-for="tag in selectedTags"
                :key="tag.id"
                class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium border"
                :style="{
                    backgroundColor: tag.color + '20',
                    borderColor: tag.color + '50',
                    color: tag.color
                }"
            >
                {{ tag.name }}
                <button
                    type="button"
                    @click="removeTag(tag.id)"
                    class="hover:opacity-70"
                >
                    <X class="h-3 w-3" />
                </button>
            </div>
        </div>

        <Popover v-model:open="isOpen">
            <PopoverTrigger as-child>
                <Button type="button" variant="outline" size="sm" class="gap-2">
                    <Plus class="h-4 w-4" />
                    Добавить теги
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-64 p-3 bg-zinc-950 border-zinc-800" align="start">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Поиск тегов..."
                    class="w-full px-2 py-1 mb-2 text-sm bg-zinc-900 border border-zinc-800 rounded text-zinc-100 placeholder-zinc-500"
                />
                <div class="space-y-1 max-h-60 overflow-y-auto">
                    <label
                        v-for="tag in filteredTags"
                        :key="tag.id"
                        class="flex items-center gap-2 p-2 rounded hover:bg-zinc-900 cursor-pointer text-sm"
                    >
                        <input
                            type="checkbox"
                            :checked="isSelected(tag.id)"
                            @change="toggleTag(tag.id)"
                            class="rounded bg-zinc-900 border-zinc-700 text-blue-600 focus:ring-blue-600"
                        />
                        <div
                            class="w-3 h-3 rounded"
                            :style="{ backgroundColor: tag.color }"
                        ></div>
                        <span class="text-zinc-300">{{ tag.name }}</span>
                    </label>
                    <div v-if="!filteredTags.length" class="text-center text-zinc-500 text-xs py-2">
                        Теги не найдены
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    </div>
</template>
