<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';

const props = defineProps<{
    total: number;
    perPage: number;
    currentPage: number;
    links: { url: string | null; label: string; active: boolean }[];
    prevPageUrl: string | null;
    nextPageUrl: string | null;
}>();

const numericLinks = computed(() => props.links.slice(1, -1));
const hasMultiplePages = computed(() => Math.ceil(props.total / props.perPage) > 1);
</script>

<template>
    <div v-if="hasMultiplePages" class="flex justify-center sm:col-start-2 sm:col-end-2">
        <Pagination
            v-slot="{ page }"
            :items-per-page="perPage"
            :total="total"
            :default-page="1"
            :page="currentPage"
        >
            <PaginationContent>
                <PaginationItem :value="currentPage - 1 || 1">
                    <Link v-if="prevPageUrl" :href="prevPageUrl" preserve-state preserve-scroll>
                        <PaginationPrevious />
                    </Link>
                    <PaginationPrevious v-else class="pointer-events-none opacity-50" />
                </PaginationItem>

                <template v-for="(link, idx) in numericLinks" :key="idx">
                    <PaginationItem v-if="link.label === '...'" :value="idx">
                        <PaginationEllipsis />
                    </PaginationItem>
                    <PaginationItem v-else :value="Number(link.label)" :is-active="link.active">
                        <Link
                            :href="link.url || '#'"
                            preserve-state
                            preserve-scroll
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium h-9 w-9 border transition-colors"
                            :class="{
                                'bg-blue-600 text-white border-blue-600': link.active,
                                'bg-zinc-900 text-zinc-200 border-zinc-800 hover:bg-zinc-800': !link.active && link.url,
                                'pointer-events-none opacity-30': !link.url
                            }"
                            v-html="link.label"
                        />
                    </PaginationItem>
                </template>

                <PaginationItem :value="currentPage + 1">
                    <Link v-if="nextPageUrl" :href="nextPageUrl" preserve-state preserve-scroll>
                        <PaginationNext />
                    </Link>
                    <PaginationNext v-else class="pointer-events-none opacity-50" />
                </PaginationItem>
            </PaginationContent>
        </Pagination>
    </div>
</template>
