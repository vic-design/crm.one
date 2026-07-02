export interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions: Permission[];
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface RoleListResponse {
    data: Role[];
    links: PaginationLink[];
    total: number;
    per_page: number;
    current_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}
