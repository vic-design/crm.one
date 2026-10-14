<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
    fallbackUrl?: string;
    showAfter?: number;
}>(), {
    showAfter: 200,
});

const visible = ref(false);

const handleScroll = () => {
    visible.value = window.scrollY > props.showAfter;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else if (props.fallbackUrl) {
        router.visit(props.fallbackUrl);
    }
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-4 opacity-0"
    >
        <Button
            v-if="visible"
            variant="outline"
            size="icon"
            class="fixed bottom-6 right-6 z-50 h-12 w-12 rounded-full shadow-lg bg-zinc-900 border-zinc-700 hover:bg-zinc-800"
            @click="goBack"
        >
            <ArrowLeft class="h-5 w-5" />
        </Button>
    </Transition>
</template>
