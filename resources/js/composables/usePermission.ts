import { usePage } from "@inertiajs/vue3";

export function usePermission() {
    const page = usePage();

    const can = (permission: string): boolean => {
        const permissions = page.props.auth?.user?.permissions || [];
        return permissions.includes(permission);
    }

    const hasRole = (role: string): boolean => {
        const roles = page.props.auth?.user?.roles || [];
        return roles.includes(role);
    }

    return { can, hasRole };
}
