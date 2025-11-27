<?php

namespace App\Services\Admin;

use App\Contracts\AdminBlogPostServiceInterface;
use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AdminBlogPostService implements AdminBlogPostServiceInterface
{
    /**
     * Get all blog posts with optional filters and pagination.
     *
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getAllPosts(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = BlogPost::query()
            ->with(['category', 'previewImage']);

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['blog_category_id'])) {
            $query->where('blog_category_id', $filters['blog_category_id']);
        }

        $query->orderBy('created_at', 'desc');

        if (!empty($filters['per_page'])) {
            return $query->paginate($filters['per_page']);
        }

        return $query->get();
    }

    /**
     * Get a blog post by ID.
     *
     * @param int $id
     * @return BlogPost
     * @throws \Exception
     */
    public function getPostById(int $id): BlogPost
    {
        $post = BlogPost::with(['category', 'previewImage', 'products'])->find($id);

        if (!$post) {
            throw new \Exception('Blog post not found');
        }

        return $post;
    }

    /**
     * Create a new blog post.
     *
     * @param array $data
     * @return BlogPost
     */
    public function createPost(array $data): BlogPost
    {
        $post = BlogPost::create($data);

        if (!empty($data['product_ids'])) {
            $this->syncProductsWithOrder($post, $data['product_ids']);
        }

        return $post->load(['category', 'previewImage', 'products']);
    }

    /**
     * Update a blog post.
     *
     * @param int $id
     * @param array $data
     * @return BlogPost
     * @throws \Exception
     */
    public function updatePost(int $id, array $data): BlogPost
    {
        $post = $this->getPostById($id);
        $post->update($data);

        if (array_key_exists('product_ids', $data)) {
            $this->syncProductsWithOrder($post, $data['product_ids'] ?? []);
        }

        return $post->fresh(['category', 'previewImage', 'products']);
    }

    /**
     * Delete a blog post.
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deletePost(int $id): bool
    {
        $post = $this->getPostById($id);

        return $post->delete();
    }

    /**
     * Sync products for a blog post.
     *
     * @param int $postId
     * @param array $productIds
     * @return BlogPost
     * @throws \Exception
     */
    public function syncProducts(int $postId, array $productIds): BlogPost
    {
        $post = $this->getPostById($postId);

        $this->syncProductsWithOrder($post, $productIds);

        return $post->fresh(['category', 'previewImage', 'products']);
    }

    /**
     * Sync products with sort order.
     *
     * @param BlogPost $post
     * @param array $productIds
     * @return void
     */
    private function syncProductsWithOrder(BlogPost $post, array $productIds): void
    {
        $syncData = [];
        foreach ($productIds as $index => $productId) {
            $syncData[$productId] = ['sort_order' => $index];
        }

        $post->products()->sync($syncData);
    }
}
