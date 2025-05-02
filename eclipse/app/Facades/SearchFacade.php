<?php

declare(strict_types=1);

namespace App\Facades;

use App\Repository\AddonRepository;
use App\Repository\TagRepository;
use App\Repository\CategoryRepository;
use App\Repository\AuthorRepository;

class SearchFacade
{
    private AddonRepository $addonRepository;
    private TagRepository $tagRepository;
    private CategoryRepository $categoryRepository;
    private AuthorRepository $authorRepository;

    public function __construct(
        AddonRepository $addonRepository,
        TagRepository $tagRepository,
        CategoryRepository $categoryRepository,
        AuthorRepository $authorRepository
    ) {
        $this->addonRepository = $addonRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->authorRepository = $authorRepository;
    }

    /**
     * Základní vyhledávání doplňků
     */
    public function searchAddons(string $query, int $page = 1, int $itemsPerPage = 10): array
    {
        return $this->addonRepository->search($query, $page, $itemsPerPage);
    }

    /**
     * Vyhledávání doplňků s filtrováním
     */
    public function advancedSearch(array $params): array
    {
        $page = $params['page'] ?? 1;
        $itemsPerPage = $params['itemsPerPage'] ?? 10;
        
        $query = $params['query'] ?? '';
        $categoryId = $params['category_id'] ?? null;
        $tagId = $params['tag_id'] ?? null;
        $authorId = $params['author_id'] ?? null;
        
        // Základní vyhledávání podle klíčového slova
        if (!empty($query)) {
            return $this->addonRepository->search($query, $page, $itemsPerPage);
        }
        
        // Vyhledávání podle kategorie
        if ($categoryId) {
            return $this->addonRepository->findByCategory((int)$categoryId, $page, $itemsPerPage);
        }
        
        // Vyhledávání podle tagu
        if ($tagId) {
            return $this->tagRepository->findAddonsByTag((int)$tagId, $page, $itemsPerPage);
        }
        
        // Vyhledávání podle autora
        if ($authorId) {
            return $this->addonRepository->findByAuthor((int)$authorId, $page, $itemsPerPage);
        }
        
        // Pokud nejsou zadány žádné parametry, vracíme nejnovější doplňky
        return [
            'items' => $this->addonRepository->findNewest($itemsPerPage),
            'totalCount' => $this->addonRepository->count(),
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'pages' => ceil($this->addonRepository->count() / $itemsPerPage)
        ];
    }

    /**
     * Hledání v různých entitách najednou
     */
    public function globalSearch(string $query): array
    {
        // Hledání doplňků
        $addons = $this->addonRepository->search($query, 1, 5);
        
        // Hledání kategorií
        $categories = [];
        foreach ($this->categoryRepository->findBy(['name LIKE ?' => "%{$query}%"]) as $category) {
            $categories[] = $category;
            if (count($categories) >= 5) {
                break;
            }
        }
        
        // Hledání tagů
        $tags = [];
        foreach ($this->tagRepository->findBy(['name LIKE ?' => "%{$query}%"]) as $tag) {
            $tags[] = $tag;
            if (count($tags) >= 5) {
                break;
            }
        }
        
        // Hledání autorů
        $authors = [];
        foreach ($this->authorRepository->findBy(['name LIKE ?' => "%{$query}%"]) as $author) {
            $authors[] = $author;
            if (count($authors) >= 5) {
                break;
            }
        }
        
        return [
            'addons' => $addons['items'],
            'categories' => $categories,
            'tags' => $tags,
            'authors' => $authors,
            'totalAddons' => $addons['totalCount'],
            'totalCategories' => count($this->categoryRepository->findBy(['name LIKE ?' => "%{$query}%"])),
            'totalTags' => count($this->tagRepository->findBy(['name LIKE ?' => "%{$query}%"])),
            'totalAuthors' => count($this->authorRepository->findBy(['name LIKE ?' => "%{$query}%"]))
        ];
    }

    /**
     * Hledání populárních klíčových slov
     */
    public function getPopularSearchTerms(int $limit = 10): array
    {
        // V reálné aplikaci byste museli implementovat sledování hledaných výrazů
        // Pro zjednodušení vrátíme nejpoužívanější tagy
        $tags = $this->tagRepository->getTagsWithCounts();
        
        // Seřazení podle počtu doplňků
        usort($tags, function($a, $b) {
            return $b['addon_count'] <=> $a['addon_count'];
        });
        
        return array_slice($tags, 0, $limit);
    }

    /**
     * Hledání podobných doplňků
     */
    public function findSimilarAddons(int $addonId, int $limit = 5): array
    {
        // Získání tagu doplňku
        $addonData = $this->addonRepository->getWithRelated($addonId);
        if (!$addonData) {
            return [];
        }
        
        $addon = $addonData['addon'];
        $tags = $addonData['tags'] ?? [];
        $category = $addonData['category'] ?? null;
        
        $tagIds = [];
        foreach ($tags as $tag) {
            $tagIds[] = $tag->id;
        }
        
        $categoryId = $category ? $category->id : null;
        
        // Logika pro hledání podobných doplňků (podle tagů a kategorie)
        // V reálné aplikaci byste museli implementovat složitější algoritmus
        // Pro zjednodušení vrátíme doplňky ze stejné kategorie
        
        if ($categoryId) {
            $result = $this->addonRepository->findByCategory($categoryId, 1, $limit + 1);
            
            // Odstranění tohoto doplňku ze seznamu
            $similarAddons = [];
            $count = 0;
            
            foreach ($result['items'] as $similarAddon) {
                if ($similarAddon->id != $addonId && $count < $limit) {
                    $similarAddons[] = $similarAddon;
                    $count++;
                }
            }
            
            return $similarAddons;
        }
        
        return [];
    }
}