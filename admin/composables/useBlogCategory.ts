import { BlogCategoryService } from '~/services/BlogCategoryService';
import type { BlogCategoryFilters, BlogCategoryFormData } from '~/models/blogCategory';

export const useBlogCategory = () => {
  const provider = new BlogCategoryService();

  return {
    getAllBlogCategories: (filters?: BlogCategoryFilters) => provider.getAllBlogCategories(filters),
    getAllBlogCategoriesPromise: (filters?: BlogCategoryFilters) => provider.getAllBlogCategoriesPromise(filters),
    getBlogCategoryById: (id: number) => provider.getBlogCategoryById(id),
    createBlogCategory: (payload: BlogCategoryFormData) => provider.createBlogCategory(payload),
    updateBlogCategory: (id: number, payload: Partial<BlogCategoryFormData>) => provider.updateBlogCategory(id, payload),
    deleteBlogCategory: (id: number) => provider.deleteBlogCategory(id),
    generateSlug: (name: string) => provider.generateSlug(name),
    reorderCategories: (categoryIds: number[]) => provider.reorderCategories(categoryIds),
  };
};
