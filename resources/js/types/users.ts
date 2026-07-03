import type { User } from './auth';

export interface UserListResponse {
    data: User[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    per_page: number;
    current_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}
