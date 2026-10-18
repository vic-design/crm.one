<script setup lang="ts">
import { ref, computed } from 'vue';
import { Filter, X, Check } from 'lucide-vue-next';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    modelValue: (string | number)[];
    options: { id: string | number; name: string }[];
    label?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: (string | number)[]): void;
}>();

const isOpen = ref(false);
const hasValue = computed(() => props.modelValue.length > 0);

const toggle = (id: string | number) => {
    const isSelected = props.modelValue.includes(id);
    const newValue = isSelected
        ? props.modelValue.filter(item => item !== id)
        : [...props.modelValue, id];

    emit('update:modelValue', newValue);
};

const clear = (event: Event) => {
    event.stopPropagation();
    emit('update:modelValue', []);
};
</script>

<template>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <Button variant="outline" class="gap-2 h-9">
                <Filter class="h-4 w-4 text-zinc-500" />
                <span>{{ label || 'Роли' }}</span>
                <Badge v-if="hasValue" variant="secondary" class="h-5 min-w-5 px-1.5 flex items-center justify-center text-xs">
                    {{ modelValue.length }}
                </Badge>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-56 p-3 bg-zinc-950 border-zinc-800" align="start">
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-zinc-800 pb-2">
                    <span class="text-sm font-medium text-zinc-300">{{ label || 'Фильтр по ролям' }}</span>
                    <Button
                        v-if="hasValue"
                        variant="ghost"
                        size="icon"
                        class="h-6 w-6 text-zinc-500 hover:text-zinc-100"
                        @click="clear"
                    >
                        <X class="h-3.5 w-3.5" />
                    </Button>
                </div>

                <div class="space-y-2 max-h-60 overflow-y-auto">
                    <div
                        v-for="option in options"
                        :key="option.id"
                        class="flex items-center justify-between p-2 rounded-md transition-colors cursor-pointer"
                        :class="modelValue.includes(option.id) ? 'bg-zinc-800 border border-zinc-700' : 'hover:bg-zinc-900/50 border border-transparent'"
                        @click="toggle(option.id)"
                    >
                        <span class="text-sm text-zinc-200 select-none">
                            {{ option.name }}
                        </span>
                        <Check v-if="modelValue.includes(option.id)" class="h-4 w-4 text-blue-500" />
                    </div>

                    <div v-if="options.length === 0" class="text-sm text-zinc-500 text-center py-2">
                        Нет доступных опций
                    </div>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
