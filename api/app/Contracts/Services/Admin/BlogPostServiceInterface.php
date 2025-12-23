<?php

namespace App\Contracts\Services\Admin;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BlogPostServiceInterface
{
    /**
     * Get all blog posts with optional filters and pagination.
     *
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getAllPosts(array $filters = []): Collection|LengthAwarePaginator;

    /**
     * Get a blog post by ID.
     *
     * @param int $id
     * @return BlogPost
     */
    public function getPostById(int $id): BlogPost;

    /**
     * Create a new blog post.
     *
     * @param array $data
     * @return BlogPost
     */
    public function createPost(array $data): BlogPost;

    /**
     * Update a blog post.
     *
     * @param int $id
     * @param array $data
     * @return BlogPost
     */
    public function updatePost(int $id, array $data): BlogPost;

    /**
     * Delete a blog post.
     *
     * @param int $id
     * @return bool
     */
    public function deletePost(int $id): bool;

    /**
     * Sync products for a blog post.
     *
     * @param int $postId
     * @param array $productIds
     * @return BlogPost
     */
    public function syncProducts(int $postId, array $productIds): BlogPost;
}
