export interface Tag {
    id: number;
    name: string;
    slug: string;
    color: string;
    created_at: string;
    updated_at: string;
    total_usage_count?: number;
}

export interface TagFilters {
    search?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    per_page?: number;
}

export interface TagListResponse {
    data: Tag[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    per_page: number;
    current_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}
