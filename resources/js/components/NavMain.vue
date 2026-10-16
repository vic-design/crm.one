<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { ChevronRight } from 'lucide-vue-next';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubItem,
    SidebarMenuSubButton,
} from '@/components/ui/sidebar';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import type { NavItem } from '@/types';

const props = defineProps<{
    items: NavItem[];
}>();

const page = usePage();

// Извлекаем строку URL из href (может быть строка, объект или функция)
const extractUrl = (href: any): string => {
    if (!href) return '';
    if (typeof href === 'string') return href;
    if (typeof href === 'object' && href.url) return href.url;
    if (typeof href === 'function') return href();
    return String(href);
};

// Улучшенная проверка URL
const isCurrentUrl = (href: any) => {
    const url = extractUrl(href);
    if (!url) return false;

    const currentPath = page.url.split('?')[0];
    const targetPath = url.split('?')[0];

    return currentPath === targetPath || currentPath.startsWith(targetPath + '/');
};

// Проверяем, активен ли хотя бы один дочерний элемент группы
const hasActiveChild = (item: NavItem) => {
    if (!item.children) return false;
    return item.children.some(child => isCurrentUrl(child.href));
};

// Состояние открытых/закрытых выпадающих списков
const openStates = ref<Record<string, boolean>>({});

// Инициализация состояний при монтировании
const initOpenStates = () => {
    props.items.forEach(item => {
        if (item.children) {
            openStates.value[item.title] = hasActiveChild(item);
        }
    });
};

onMounted(initOpenStates);

// Отслеживаем изменения URL и автоматически раскрываем группу
watch(() => page.url, () => {
    props.items.forEach(item => {
        if (item.children && hasActiveChild(item)) {
            openStates.value[item.title] = true;
        }
    });
});
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Навигация</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Обычный пункт меню -->
                <SidebarMenuItem v-if="!item.children">
                    <SidebarMenuButton
                        as-child
                        :is-active="isCurrentUrl(item.href)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href || ''">
                            <component :is="item.icon" v-if="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                <!-- Выпадающий пункт меню -->
                <Collapsible
                    v-else
                    :key="item.title"
                    :open="openStates[item.title]"
                    @update:open="(val: boolean) => openStates[item.title] = val"
                    class="group/collapsible"
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :tooltip="item.title">
                                <component :is="item.icon" v-if="item.icon" class="h-4 w-4" />
                                <span>{{ item.title }}</span>
                                <ChevronRight class="ml-auto h-4 w-4 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem v-for="child in item.children" :key="child.title">
                                    <SidebarMenuSubButton
                                        as-child
                                        :is-active="isCurrentUrl(child.href)"
                                    >
                                        <Link :href="child.href || ''">
                                            <span>{{ child.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
