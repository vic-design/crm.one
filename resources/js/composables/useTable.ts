// resources/js/composables/useTable.ts
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export function useTable<T extends Record<string, any>>(
    url: string,
    initialFilters: Partial<T> = {}
) {
    const filters = ref<Partial<T>>({ ...initialFilters });
    const sortColumn = ref<string>((initialFilters.sort as string) || 'id');
    const sortDirection = ref<'asc' | 'desc'>((initialFilters.direction as 'asc' | 'desc') || 'asc');

    let timeout: ReturnType<typeof setTimeout>;

    const applyFilters = (immediate = false) => {
        const execute = () => {
            router.get(url, {
                ...filters.value,
                sort: sortColumn.value,
                direction: sortDirection.value,
            }, {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            });
        };

        if (immediate) {
            clearTimeout(timeout);
            execute();
            return;
        }

        clearTimeout(timeout);
        timeout = setTimeout(execute, 300);
    };

    watch(filters, () => applyFilters(), { deep: true });

    const toggleSort = (column: string) => {
        if (sortColumn.value === column) {
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn.value = column;
            sortDirection.value = 'asc';
        }
        applyFilters(true);
    };

    // Сбрасываем только фильтры, сортировку не трогаем
    const resetFilters = () => {
        filters.value = {};
        applyFilters(true);
    };

    return {
        filters,
        sortColumn,
        sortDirection,
        applyFilters,
        toggleSort,
        resetFilters,
    };
}
