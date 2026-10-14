import type { Role } from './roles';
import type { Tag } from './tags';

export type User = {
    id: number;
    name: string;
    email: string;
    avatar_url?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    permissions: string[];
    roles: Pick<Role, 'id' | 'name'>[];
    tags: Tag[];
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
