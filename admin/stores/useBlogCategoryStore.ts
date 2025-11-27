import { defineStore } from 'pinia';
import type { BlogCategoryFormData, BlogCategoryFilters } from '~/models/blogCategory';

export const useBlogCategoryStore = defineStore('blogCategory', () => {
  const {
    getAllBlogCategories,
    getAllBlogCategoriesPromise,
    getBlogCategoryById,
    createBlogCategory,
    updateBlogCategory,
    deleteBlogCategory,
    generateSlug,
    reorderCategories,
  } = useBlogCategory();

  return {
    fetchBlogCategories: async (filters?: BlogCategoryFilters) => await getAllBlogCategories(filters),
    fetchBlogCategoriesPromise: (filters?: BlogCategoryFilters) => getAllBlogCategoriesPromise(filters),
    fetchBlogCategoryById: async (id: number) => await getBlogCategoryById(id),
    onCreateBlogCategory: async (payload: BlogCategoryFormData) => await createBlogCategory(payload),
    onUpdateBlogCategory: async (id: number, payload: Partial<BlogCategoryFormData>) => await updateBlogCategory(id, payload),
    onDeleteBlogCategory: async (id: number) => await deleteBlogCategory(id),
    onGenerateSlug: async (name: string) => await generateSlug(name),
    onReorderCategories: async (categoryIds: number[]) => await reorderCategories(categoryIds),
  };
});
