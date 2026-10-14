import { router } from '@inertiajs/vue3';

export function useBack(fallbackUrl: string) {
    const goBack = () => {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            router.visit(fallbackUrl);
        }
    };

    return { goBack };
}
