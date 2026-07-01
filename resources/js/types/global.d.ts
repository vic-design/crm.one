import type { Auth } from '@/types/auth';
import { SharedProps } from '@inertiajs/vue3';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/vue3' {
    export interface SharedProps {
        name: string,
        auth: Auth,
        sidebarOpen: boolean,
        [key: string]: unknown,
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    };
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: {
            props: SharedProps;
            url: string;
            component: string;
            version: string | null;
            scrollRegions: Array<{ top: number; left: number }>;
            rememberedState: Record<string, unknown>;
        };
        $headManager: ReturnType<typeof createHeadManager>;
    }
}
