<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Category;
use App\Repository\CategoryRepository;
use App\Collection\Collection;

/**
 * Category service implementation
 * 
 * @extends BaseService<Category>
 * @implements ICategoryService
 */
class CategoryService extends BaseService implements ICategoryService
{
    /** @var CategoryRepository */
    private CategoryRepository $categoryRepository;
    
    /**
     * Constructor
     * 
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        parent::__construct();
        $this->categoryRepository = $categoryRepository;
        $this->entityClass = Category::class;
    }
    
    /**
     * Get repository for entity
     * 
     * @return CategoryRepository
     */
    protected function getRepository(): CategoryRepository
    {
        return $this->categoryRepository;
    }
    
    /**
     * Find category by slug
     * 
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }
    
    /**
     * Find root categories
     * 
     * @return Collection<Category>
     */
    public function findRootCategories(): Collection
    {
        return $this->categoryRepository->findRootCategories();
    }
    
    /**
     * Find subcategories
     * 
     * @param int $parentId
     * @return Collection<Category>
     */
    public function findSubcategories(int $parentId): Collection
    {
        return $this->categoryRepository->findSubcategories($parentId);
    }
    
    /**
     * Find all subcategories recursively
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function findAllSubcategoriesRecursive(int $categoryId): Collection
    {
        return $this->categoryRepository->findAllSubcategoriesRecursive($categoryId);
    }
    
    /**
     * Get category path (breadcrumbs)
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function getCategoryPath(int $categoryId): Collection
    {
        return $this->categoryRepository->getCategoryPath($categoryId);
    }
    
    /**
     * Get category hierarchy
     * 
     * @return array
     */
    public function getHierarchy(): array
    {
        return $this->categoryRepository->getHierarchy();
    }
    
    /**
     * Get most popular categories
     * 
     * @param int $limit
     * @return array
     */
    public function getMostPopularCategories(int $limit = 10): array
    {
        return $this->categoryRepository->getMostPopularCategories($limit);
    }
    
    /**
     * Get category hierarchy with stats
     * 
     * @return array
     */
    public function getHierarchyWithStats(): array
    {
        return $this->categoryRepository->getHierarchyWithStats();
    }
}