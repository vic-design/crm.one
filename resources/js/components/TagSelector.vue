<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import type { Tag } from '@/types';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import Button from '@/components/ui/button/Button.vue';
import { X, Plus, Check } from 'lucide-vue-next';

const props = defineProps<{
    modelValue: number[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number[]): void;
}>();

const allTags = ref<Tag[]>([]);
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
</script>

<template>
    <div class="space-y-2">
        <!-- Выбранные теги -->
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
                <button type="button" @click="removeTag(tag.id)" class="hover:opacity-70">
                    <X class="h-3 w-3" />
                </button>
            </div>
        </div>

        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button type="button" variant="outline" size="sm" class="gap-2">
                    <Plus class="h-4 w-4" />
                    Добавить теги
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent
                class="w-64 bg-zinc-950 border-zinc-800"
                align="start"
                @select.prevent
            >
                <!-- Поиск -->
                <div class="p-2">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Поиск тегов..."
                        class="w-full px-2 py-1 text-sm bg-zinc-900 border border-zinc-800 rounded text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-700"
                        @pointer-down.stop
                        @click.stop
                    />
                </div>

                <!-- Список тегов -->
                <div class="max-h-60 overflow-y-auto px-1 pb-1">
                    <div
                        v-for="tag in filteredTags"
                        :key="tag.id"
                        class="flex items-center gap-2 p-2 rounded hover:bg-zinc-900 cursor-pointer text-sm"
                        @click.stop="toggleTag(tag.id)"
                    >
                        <div
                            class="w-4 h-4 rounded border flex items-center justify-center"
                            :class="isSelected(tag.id) ? 'bg-blue-600 border-blue-600' : 'bg-zinc-900 border-zinc-700'"
                        >
                            <Check v-if="isSelected(tag.id)" class="h-3 w-3 text-white" />
                        </div>
                        <div class="w-3 h-3 rounded" :style="{ backgroundColor: tag.color }"></div>
                        <span class="text-zinc-300">{{ tag.name }}</span>
                    </div>

                    <div v-if="!filteredTags.length" class="text-center text-zinc-500 text-xs py-2">
                        Теги не найдены
                    </div>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
