<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Category;
use Nette\Database\Explorer;
use Nette\Utils\Strings;

class CategoryRepository extends BaseRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'categories';
        $this->entityClass = Category::class;
    }

    /**
     * Find category by slug
     * 
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Get root categories
     * 
     * @return array
     */
    public function findRootCategories(): array
    {
        $rows = $this->findBy(['parent_id' => null]);
        $categories = [];
        
        foreach ($rows as $row) {
            $categories[] = Category::fromArray($row->toArray());
        }
        
        return $categories;
    }

    /**
     * Get subcategories of a category
     * 
     * @param int $parentId
     * @return array
     */
    public function findSubcategories(int $parentId): array
    {
        $rows = $this->findBy(['parent_id' => $parentId]);
        $categories = [];
        
        foreach ($rows as $row) {
            $categories[] = Category::fromArray($row->toArray());
        }
        
        return $categories;
    }

    /**
     * Get category hierarchy
     * 
     * @return array
     */
    public function getHierarchy(): array
    {
        $categoryRows = $this->findAll()->fetchAll();
        $categories = [];
        
        foreach ($categoryRows as $row) {
            $categories[] = Category::fromArray($row->toArray());
        }
        
        $hierarchy = [];
        
        // First get all root categories
        foreach ($categories as $category) {
            if ($category->parent_id === null) {
                $hierarchyItem = $category->toArray();
                $hierarchyItem['subcategories'] = [];
                $hierarchy[$category->id] = $hierarchyItem;
            }
        }
        
        // Then assign subcategories
        foreach ($categories as $category) {
            if ($category->parent_id !== null && isset($hierarchy[$category->parent_id])) {
                $hierarchy[$category->parent_id]['subcategories'][] = $category->toArray();
            }
        }
        
        return array_values($hierarchy);
    }

    /**
     * Create a new category
     * 
     * @param Category $category
     * @return int
     */
    public function create(Category $category): int
    {
        // Generate slug if not provided
        if (empty($category->slug)) {
            $category->slug = Strings::webalize($category->name);
        }
        
        return $this->save($category);
    }

    /**
     * Update a category
     * 
     * @param Category $category
     * @return int
     */
    public function update(Category $category): int
    {
        // Update slug if name changed and slug is empty
        if (empty($category->slug)) {
            $category->slug = Strings::webalize($category->name);
        }
        
        return $this->save($category);
    }
}