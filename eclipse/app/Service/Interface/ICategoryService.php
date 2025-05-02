<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Category;
use App\Collection\Collection;

/**
 * Interface for category service
 */
interface ICategoryService extends IBaseService
{
    /**
     * Find category by slug
     * 
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category;
    
    /**
     * Find root categories
     * 
     * @return Collection<Category>
     */
    public function findRootCategories(): Collection;
    
    /**
     * Find subcategories
     * 
     * @param int $parentId
     * @return Collection<Category>
     */
    public function findSubcategories(int $parentId): Collection;
    
    /**
     * Find all subcategories recursively
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function findAllSubcategoriesRecursive(int $categoryId): Collection;
    
    /**
     * Get category path (breadcrumbs)
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function getCategoryPath(int $categoryId): Collection;
    
    /**
     * Get category hierarchy
     * 
     * @return array
     */
    public function getHierarchy(): array;
    
    /**
     * Get most popular categories
     * 
     * @param int $limit
     * @return array
     */
    public function getMostPopularCategories(int $limit = 10): array;
    
    /**
     * Get category hierarchy with stats
     * 
     * @return array
     */
    public function getHierarchyWithStats(): array;
}