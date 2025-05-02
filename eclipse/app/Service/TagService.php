<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Tag;
use App\Repository\TagRepository;
use App\Collection\PaginatedCollection;

/**
 * Tag service implementation
 * 
 * @extends BaseService<Tag>
 * @implements ITagService
 */
class TagService extends BaseService implements ITagService
{
    /** @var TagRepository */
    private TagRepository $tagRepository;
    
    /**
     * Constructor
     * 
     * @param TagRepository $tagRepository
     */
    public function __construct(TagRepository $tagRepository)
    {
        parent::__construct();
        $this->tagRepository = $tagRepository;
        $this->entityClass = Tag::class;
    }
    
    /**
     * Get repository for entity
     * 
     * @return TagRepository
     */
    protected function getRepository(): TagRepository
    {
        return $this->tagRepository;
    }
    
    /**
     * Find tag by slug
     * 
     * @param string $slug
     * @return Tag|null
     */
    public function findBySlug(string $slug): ?Tag
    {
        return $this->tagRepository->findBySlug($slug);
    }
    
    /**
     * Find or create a tag
     * 
     * @param string $name
     * @return int ID of the tag
     */
    public function findOrCreate(string $name): int
    {
        return $this->tagRepository->findOrCreate($name);
    }
    
    /**
     * Get tags with counts
     * 
     * @return array
     */
    public function getTagsWithCounts(): array
    {
        return $this->tagRepository->getTagsWithCounts();
    }
    
    /**
     * Find addons by tag
     * 
     * @param int $tagId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection
     */
    public function findAddonsByTag(int $tagId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->tagRepository->findAddonsByTag($tagId, $page, $itemsPerPage);
    }
    
    /**
     * Find related tags
     * 
     * @param int $tagId
     * @param int $limit
     * @return array
     */
    public function findRelatedTags(int $tagId, int $limit = 10): array
    {
        return $this->tagRepository->findRelatedTags($tagId, $limit);
    }
    
    /**
     * Get trending tags
     * 
     * @param int $days Number of days to look back
     * @param int $limit Maximum number of tags to return
     * @return array
     */
    public function getTrendingTags(int $days = 30, int $limit = 10): array
    {
        return $this->tagRepository->getTrendingTags($days, $limit);
    }
    
    /**
     * Generate a weighted tag cloud
     * 
     * @param int $limit Maximum number of tags to include
     * @param int|null $categoryId Optional category ID to filter by
     * @return array
     */
    public function generateTagCloud(int $limit = 50, ?int $categoryId = null): array
    {
        return $this->tagRepository->generateTagCloud($limit, $categoryId);
    }
    
    /**
     * Find tags by multiple categories
     * 
     * @param array $categoryIds
     * @param int $limit
     * @return array
     */
    public function findTagsByCategories(array $categoryIds, int $limit = 20): array
    {
        return $this->tagRepository->findTagsByCategories($categoryIds, $limit);
    }
}