<script setup lang="ts">
import { ref, watch } from 'vue';
import PerPageSelector from './PerPageSelector.vue';
import PaginationLinks from './PaginationLinks.vue';

const props = defineProps<{
    total: number;
    perPage: number;
    currentPage: number;
    links: { url: string | null; label: string; active: boolean }[];
    prevPageUrl: string | null;
    nextPageUrl: string | null;
    perPageOptions: number[];
}>();

const emit = defineEmits<{
    (e: 'update:perPage', value: number): void;
}>();

const currentPerPage = ref(props.perPage);

// При изменении селекта сообщаем наверх
watch(currentPerPage, (newVal) => {
    emit('update:perPage', newVal);
});

// Если значение пришло с сервера (например, после сброса фильтров), обновляем локальное состояние
watch(() => props.perPage, (newVal) => {
    if (newVal !== currentPerPage.value) {
        currentPerPage.value = newVal;
    }
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4 mt-4">
        <!-- Левая часть -->
        <PerPageSelector
            v-model="currentPerPage"
            :total="total"
            :options="perPageOptions"
        />

        <!-- Центральная часть -->
        <PaginationLinks
            :total="total"
            :per-page="currentPerPage"
            :current-page="currentPage"
            :links="links"
            :prev-page-url="prevPageUrl"
            :next-page-url="nextPageUrl"
        />

        <!-- Правая часть (для симметрии grid) -->
        <div class="hidden sm:block"></div>
    </div>
</template>
