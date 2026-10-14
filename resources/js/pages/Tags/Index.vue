<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Tag, TagListResponse, TagFilters } from '@/types';
import tags from '@/routes/tags';
import Input from '@/components/ui/input/Input.vue';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
    Pagination, PaginationContent, PaginationEllipsis, PaginationItem,
    PaginationNext, PaginationPrevious,
} from '@/components/ui/pagination';
import Button from '@/components/ui/button/Button.vue';
import { Pencil, Plus, Trash, ArrowUp, ArrowDown, ArrowUpDown } from 'lucide-vue-next';
import { useTable } from '@/composables/useTable';
import TagCreateDialog from './Partials/TagCreateDialog.vue';
import TagEditDialog from './Partials/TagEditDialog.vue';
import TagDeleteDialog from './Partials/TagDeleteDialog.vue';

const props = defineProps<{
    tagList: TagListResponse;
    filters?: TagFilters;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Теги', href: tags.index.url() }];

const {
    filters,
    sortColumn,
    sortDirection,
    toggleSort,
} = useTable<TagFilters>(tags.index.url(), props.filters);

const createDialogRef = ref<InstanceType<typeof TagCreateDialog> | null>(null);
const editDialogRef = ref<InstanceType<typeof TagEditDialog> | null>(null);
const deleteDialogRef = ref<InstanceType<typeof TagDeleteDialog> | null>(null);
const currentTag = ref<Tag | null>(null);

const openEdit = (tag: Tag) => {
    currentTag.value = tag;
    editDialogRef.value?.openDialog(tag);
};

const numericLinks = computed(() => props.tagList.links.slice(1, -1));
const getPageNumber = (index: number) => {
    return (props.tagList.current_page - 1) * props.tagList.per_page + index + 1;
};
</script>

<template>
    <Head title="Теги" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="grid grid-cols-3 content-start gap-4">
            <div class="flex h-full col-span-3 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="w-full max-w-sm">
                        <Input
                            v-model="filters.search"
                            type="text"
                            placeholder="Поиск по названию..."
                            class="w-full"
                        />
                    </div>

                    <Button type="button" @click="createDialogRef?.openDialog()">
                        <Plus class="mr-2 h-4 w-4" /> Создать
                    </Button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[80px]">№</TableHead>
                            <TableHead
                                class="cursor-pointer select-none hover:bg-zinc-800/50 transition-colors"
                                @click="toggleSort('name')"
                            >
                                <div class="flex items-center gap-2">
                                    Название
                                    <ArrowUpDown v-if="sortColumn !== 'name'" class="h-4 w-4 text-zinc-500" />
                                    <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4 text-blue-500" />
                                    <ArrowDown v-else class="h-4 w-4 text-blue-500" />
                                </div>
                            </TableHead>
                            <TableHead>Цвет</TableHead>
                            <TableHead class="text-right">Использований</TableHead>
                            <TableHead class="text-right">Действия</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(tag, index) in tagList?.data" :key="tag.id">
                            <TableCell class="font-medium">{{ getPageNumber(index) }}</TableCell>
                            <TableCell class="font-medium text-white">
                                <Link :href="tags.show.url({ tag: tag.id })" class="hover:text-blue-400 transition-colors">
                                    {{ tag.name }}
                                </Link>
                            </TableCell>
                            <TableCell>
                                <div
                                    class="w-8 h-8 rounded border border-zinc-700"
                                    :style="{ backgroundColor: tag.color }"
                                ></div>
                            </TableCell>
                            <TableCell class="text-right">
                                <span class="text-zinc-400">
                                    {{ tag.total_usage_count || 0 }}
                                </span>
                            </TableCell>
                            <TableCell class="text-right whitespace-nowrap space-x-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    @click="openEdit(tag)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="icon"
                                    @click="deleteDialogRef?.openDialog(tag.id, tag.name)"
                                >
                                    <Trash class="h-4 w-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!tagList?.data?.length">
                            <TableCell colspan="5" class="h-24 text-center text-zinc-500">
                                Теги не найдены
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div
                    class="flex flex-col gap-6"
                    v-if="tagList?.total > tagList?.per_page"
                >
                    <Pagination
                        v-slot="{ page }"
                        :items-per-page="tagList?.per_page"
                        :total="tagList?.total"
                        :default-page="1"
                        :page="tagList?.current_page"
                        class="flex justify-center"
                    >
                        <PaginationContent>
                            <PaginationItem :value="tagList.current_page - 1 || 1">
                                <Link
                                    v-if="tagList?.prev_page_url"
                                    :href="tagList.prev_page_url"
                                    preserve-state
                                    preserve-scroll
                                >
                                    <PaginationPrevious />
                                </Link>
                                <PaginationPrevious v-else class="pointer-events-none opacity-50" />
                            </PaginationItem>

                            <template v-for="(link, idx) in numericLinks" :key="idx">
                                <PaginationItem v-if="link.label === '...'" :value="idx">
                                    <PaginationEllipsis />
                                </PaginationItem>
                                <PaginationItem
                                    v-else
                                    :value="Number(link.label)"
                                    :is-active="link.active"
                                >
                                    <Link
                                        :href="link.url ?? '#'"
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

                            <PaginationItem :value="tagList.current_page + 1">
                                <Link
                                    v-if="tagList?.next_page_url"
                                    :href="tagList.next_page_url"
                                    preserve-state
                                    preserve-scroll
                                >
                                    <PaginationNext />
                                </Link>
                                <PaginationNext v-else class="pointer-events-none opacity-50" />
                            </PaginationItem>
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>

        <TagCreateDialog ref="createDialogRef" />
        <TagEditDialog ref="editDialogRef" :tag="currentTag" />
        <TagDeleteDialog ref="deleteDialogRef" />
    </AppLayout>
</template>
