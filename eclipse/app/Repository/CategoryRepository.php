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
     * Get all subcategories recursively
     * 
     * @param int $categoryId
     * @return array
     */
    public function findAllSubcategoriesRecursive(int $categoryId): array
    {
        $result = [];
        $directSubcategories = $this->findSubcategories($categoryId);
        
        foreach ($directSubcategories as $subcategory) {
            $result[] = $subcategory;
            $childSubcategories = $this->findAllSubcategoriesRecursive($subcategory->id);
            foreach ($childSubcategories as $childSubcategory) {
                $result[] = $childSubcategory;
            }
        }
        
        return $result;
    }

    /**
     * Get complete path to category (from root to the category)
     * 
     * @param int $categoryId
     * @return array
     */
    public function getCategoryPath(int $categoryId): array
    {
        $path = [];
        $currentCategory = $this->findById($categoryId);
        
        if (!$currentCategory) {
            return $path;
        }
        
        $path[] = $currentCategory;
        
        while ($currentCategory && $currentCategory->parent_id !== null) {
            $parentCategory = $this->findById($currentCategory->parent_id);
            if ($parentCategory) {
                array_unshift($path, $parentCategory);
                $currentCategory = $parentCategory;
            } else {
                break;
            }
        }
        
        return $path;
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

    /**
     * Get most popular categories based on addon downloads
     * 
     * @param int $limit
     * @return array
     */
    public function getMostPopularCategories(int $limit = 10): array
    {
        $result = $this->database->query("
            SELECT c.id, c.name, c.slug, COUNT(a.id) as addon_count, SUM(a.downloads_count) as total_downloads
            FROM {$this->tableName} c
            JOIN addons a ON c.id = a.category_id
            GROUP BY c.id, c.name, c.slug
            ORDER BY total_downloads DESC
            LIMIT ?
        ", $limit);
        
        $categories = [];
        
        foreach ($result as $row) {
            $category = Category::fromArray([
                'id' => $row->id,
                'name' => $row->name,
                'slug' => $row->slug,
                'parent_id' => null // We don't need this for the result
            ]);
            
            $categories[] = [
                'category' => $category,
                'addon_count' => (int)$row->addon_count,
                'total_downloads' => (int)$row->total_downloads
            ];
        }
        
        return $categories;
    }

    /**
     * Get the full hierarchy of categories with statistics
     * 
     * @return array
     */
    public function getHierarchyWithStats(): array
    {
        // Get all categories
        $categories = $this->findAll()->fetchAll();
        
        // Get addon counts for all categories
        $addonCounts = $this->database->query("
            SELECT category_id, COUNT(*) as addon_count
            FROM addons
            GROUP BY category_id
        ")->fetchPairs('category_id', 'addon_count');
        
        // Build hierarchy with counts
        $hierarchy = [];
        $categoriesById = [];
        
        // First pass: create category objects with addon counts
        foreach ($categories as $row) {
            $category = Category::fromArray($row->toArray());
            $categoryData = [
                'category' => $category,
                'addon_count' => $addonCounts[$category->id] ?? 0,
                'total_addon_count' => $addonCounts[$category->id] ?? 0, // Will be updated in second pass
                'subcategories' => []
            ];
            
            $categoriesById[$category->id] = $categoryData;
        }
        
        // Second pass: build the tree
        foreach ($categoriesById as $id => $categoryData) {
            $category = $categoryData['category'];
            
            if ($category->parent_id === null) {
                // This is a root category
                $hierarchy[] = &$categoriesById[$id];
            } else {
                // This is a child category
                if (isset($categoriesById[$category->parent_id])) {
                    $categoriesById[$category->parent_id]['subcategories'][] = &$categoriesById[$id];
                }
            }
        }
        
        // Third pass: update total counts (including subcategories)
        $this->updateTotalCounts($hierarchy);
        
        return $hierarchy;
    }

    /**
     * Helper method to recursively update total addon counts
     * 
     * @param array &$categories
     * @return int
     */
    private function updateTotalCounts(array &$categories): int
    {
        $total = 0;
        
        foreach ($categories as &$categoryData) {
            $subtotal = $categoryData['addon_count'];
            
            if (!empty($categoryData['subcategories'])) {
                $subtotal += $this->updateTotalCounts($categoryData['subcategories']);
            }
            
            $categoryData['total_addon_count'] = $subtotal;
            $total += $subtotal;
        }
        
        return $total;
    }

    /**
     * Get categories with advanced filtering and sorting
     * 
     * @param array $filters Filtering criteria (e.g., ['parent_id' => 1, 'name_like' => 'test'])
     * @param string $sortBy Field to sort by
     * @param string $sortDir Sort direction (ASC or DESC)
     * @param int $page Page number
     * @param int $itemsPerPage Items per page
     * @return array
     */
    public function findFilteredCategories(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): array
    {
        $selection = $this->getTable();
        
        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'name_like':
                    $selection->where('name LIKE ?', "%{$value}%");
                    break;
                    
                case 'parent_id':
                    if ($value === 'null') {
                        $selection->where('parent_id IS NULL');
                    } else {
                        $selection->where('parent_id', $value);
                    }
                    break;
                    
                case 'has_addons':
                    if ($value) {
                        $selection->where('id IN (SELECT DISTINCT category_id FROM addons)');
                    } else {
                        $selection->where('id NOT IN (SELECT DISTINCT category_id FROM addons)');
                    }
                    break;
                    
                case 'min_addons':
                    $selection->where('id IN (
                        SELECT category_id FROM (
                            SELECT category_id, COUNT(*) as addon_count 
                            FROM addons 
                            GROUP BY category_id
                            HAVING addon_count >= ?
                        ) AS subquery
                    )', $value);
                    break;
                    
                default:
                    if (property_exists('App\Model\Category', $key)) {
                        $selection->where($key, $value);
                    }
                    break;
            }
        }
        
        // Count total matching records
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        // Apply sorting
        if (property_exists('App\Model\Category', $sortBy)) {
            $selection->order("$sortBy $sortDir");
        } else {
            $selection->order("name ASC"); // Default sorting
        }
        
        // Apply pagination
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Convert to entities
        $items = [];
        foreach ($selection as $row) {
            $items[] = Category::fromArray($row->toArray());
        }
        
        return [
            'items' => $items,
            'totalCount' => $count,
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'pages' => $pages
        ];
    }
}