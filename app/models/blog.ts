export interface BlogCategory {
  id: number;
  name: string;
  description: string | null;
  slug: string;
  status: string;
  sort_order: number;
  posts_count?: number;
  created_at: string;
  updated_at: string;
}

export interface BlogPost {
  id: number;
  title: string;
  short_description: string | null;
  slug: string;
  content: string;
  preview_image_id: number | null;
  preview_image?: {
    id: number;
    original_name: string;
    path: string;
  } | null;
  status: string;
  publication_date: string;
  blog_category_id: number;
  category?: BlogCategory;
  products?: import("./product").Product[];
  created_at: string;
  updated_at: string;
}

export interface BlogMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}
