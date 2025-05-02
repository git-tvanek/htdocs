<?php

declare(strict_types=1);

namespace App\Facades;

use App\Model\Category;
use App\Repository\CategoryRepository;
use App\Repository\AddonRepository;
use Nette\Utils\Strings;

class CategoryFacade
{
    private CategoryRepository $categoryRepository;
    private AddonRepository $addonRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        AddonRepository $addonRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->addonRepository = $addonRepository;
    }

    /**
     * Získání všech kategorií
     */
    public function getAllCategories(): array
    {
        $categories = [];
        foreach ($this->categoryRepository->findAll() as $row) {
            $categories[] = Category::fromArray($row->toArray());
        }
        return $categories;
    }

    /**
     * Získání kořenových kategorií
     */
    public function getRootCategories(): array
    {
        return $this->categoryRepository->findRootCategories();
    }

    /**
     * Získání hierarchie kategorií
     */
    public function getCategoryHierarchy(): array
    {
        return $this->categoryRepository->getHierarchy();
    }

    /**
     * Získání kategorie podle ID
     */
    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->findById($id);
    }

    /**
     * Získání kategorie podle slugu
     */
    public function getCategoryBySlug(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    /**
     * Získání doplňků v kategorii
     */
    public function getAddonsByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): array
    {
        return $this->addonRepository->findByCategory($categoryId, $page, $itemsPerPage);
    }

    /**
     * Získání doplňků v kategorii podle slugu
     */
    public function getAddonsByCategorySlug(string $slug, int $page = 1, int $itemsPerPage = 10): ?array
    {
        $category = $this->categoryRepository->findBySlug($slug);
        if (!$category) {
            return null;
        }
        
        return $this->addonRepository->findByCategory($category->id, $page, $itemsPerPage);
    }

    /**
     * Vytvoření nové kategorie
     */
    public function createCategory(array $data): int
    {
        // Generování slugu, pokud nebyl poskytnut
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Strings::webalize($data['name']);
        }
        
        $category = Category::fromArray($data);
        return $this->categoryRepository->create($category);
    }

    /**
     * Aktualizace kategorie
     */
    public function updateCategory(int $id, array $data): bool
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            return false;
        }
        
        // Generování slugu, pokud nebyl poskytnut
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Strings::webalize($data['name']);
        }
        
        // Aktualizace objektu kategorie
        foreach ($data as $key => $value) {
            if (property_exists($category, $key)) {
                $category->{$key} = $value;
            }
        }
        
        $this->categoryRepository->update($category);
        return true;
    }

    /**
     * Smazání kategorie
     */
    public function deleteCategory(int $id): bool
    {
        // Kontrola, zda kategorie nemá doplňky
        $addons = $this->addonRepository->findByCategory($id, 1, 1);
        if ($addons['totalCount'] > 0) {
            throw new \RuntimeException('Kategorie obsahuje doplňky a nelze ji smazat');
        }
        
        // Kontrola, zda kategorie nemá podkategorie
        $subcategories = $this->categoryRepository->findSubcategories($id);
        if (!empty($subcategories)) {
            throw new \RuntimeException('Kategorie obsahuje podkategorie a nelze ji smazat');
        }
        
        return $this->categoryRepository->delete($id);
    }

    /**
     * Přesunutí doplňků do jiné kategorie a smazání kategorie
     */
    public function moveToCategoryAndDelete(int $sourceId, int $targetId): bool
    {
        // Kontrola existence cílové kategorie
        $targetCategory = $this->categoryRepository->findById($targetId);
        if (!$targetCategory) {
            throw new \InvalidArgumentException('Cílová kategorie neexistuje');
        }
        
        // Získání všech doplňků ze zdrojové kategorie
        $page = 1;
        $itemsPerPage = 100;
        $allMoved = false;
        
        while (!$allMoved) {
            $addons = $this->addonRepository->findByCategory($sourceId, $page, $itemsPerPage);
            
            if (empty($addons['items'])) {
                $allMoved = true;
                continue;
            }
            
            foreach ($addons['items'] as $addon) {
                $addon->category_id = $targetId;
                $this->addonRepository->save($addon);
            }
            
            $page++;
            
            if ($page > $addons['pages']) {
                $allMoved = true;
            }
        }
        
        // Aktualizace podkategorií
        $subcategories = $this->categoryRepository->findSubcategories($sourceId);
        foreach ($subcategories as $subcategory) {
            $subcategory->parent_id = $targetId;
            $this->categoryRepository->update($subcategory);
        }
        
        // Smazání zdrojové kategorie
        return $this->categoryRepository->delete($sourceId);
    }

    /**
     * Získání podkategorií
     */
    public function getSubcategories(int $parentId): array
    {
        return $this->categoryRepository->findSubcategories($parentId);
    }

    /**
     * Přesunutí kategorie v hierarchii
     */
    public function moveCategory(int $id, ?int $parentId): bool
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            return false;
        }
        
        // Kontrola, zda nedochází k přesunu do vlastní podkategorie
        if ($parentId !== null) {
            $parent = $this->categoryRepository->findById($parentId);
            if (!$parent) {
                return false;
            }
            
            // Kontrola, zda nejde o cyklickou závislost
            $currentParent = $parent;
            while ($currentParent && $currentParent->parent_id !== null) {
                if ($currentParent->parent_id === $id) {
                    throw new \RuntimeException('Nelze přesunout kategorii do její vlastní podkategorie');
                }
                $currentParent = $this->categoryRepository->findById($currentParent->parent_id);
            }
        }
        
        $category->parent_id = $parentId;
        $this->categoryRepository->update($category);
        
        return true;
    }
}