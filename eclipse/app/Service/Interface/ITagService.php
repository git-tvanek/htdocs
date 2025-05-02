<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Tag;
use App\Collection\PaginatedCollection;

/**
 * Interface for tag service
 */
interface ITagService extends IBaseService
{
    /**
     * Find tag by slug
     * 
     * @param string $slug
     * @return Tag|null
     */
    public function findBySlug(string $slug): ?Tag;
    
    /**
     * Find or create a tag
     * 
     * @param string $name
     * @return int ID of the tag
     */
    public function findOrCreate(string $name): int;
    
    /**
     * Get tags with counts
     * 
     * @return array
     */
    public function getTagsWithCounts(): array;
    
    /**
     * Find addons by tag
     * 
     * @param int $tagId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection
     */
    public function findAddonsByTag(int $tagId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Find related tags
     * 
     * @param int $tagId
     * @param int $limit
     * @return array
     */
    public function findRelatedTags(int $tagId, int $limit = 10): array;
    
    /**
     * Get trending tags
     * 
     * @param int $days Number of days to look back
     * @param int $limit Maximum number of tags to return
     * @return array
     */
    public function getTrendingTags(int $days = 30, int $limit = 10): array;
    
    /**
     * Generate a weighted tag cloud
     * 
     * @param int $limit Maximum number of tags to include
     * @param int|null $categoryId Optional category ID to filter by
     * @return array
     */
    public function generateTagCloud(int $limit = 50, ?int $categoryId = null): array;
    
    /**
     * Find tags by multiple categories
     * 
     * @param array $categoryIds
     * @param int $limit
     * @return array
     */
    public function findTagsByCategories(array $categoryIds, int $limit = 20): array;
}