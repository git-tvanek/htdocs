<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Addon;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Interface for addon service
 */
interface IAddonService extends IBaseService
{
    /**
     * Find addon by slug
     * 
     * @param string $slug
     * @return Addon|null
     */
    public function findBySlug(string $slug): ?Addon;
    
    /**
     * Find addons by category
     * 
     * @param int $categoryId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Find addons by author
     * 
     * @param int $authorId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByAuthor(int $authorId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Find popular addons
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findPopular(int $limit = 10): Collection;
    
    /**
     * Find top rated addons
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findTopRated(int $limit = 10): Collection;
    
    /**
     * Find newest addons
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findNewest(int $limit = 10): Collection;
    
    /**
     * Search addons by keyword
     * 
     * @param string $query
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Increment download count
     * 
     * @param int $id
     * @return bool
     */
    public function incrementDownloadCount(int $id): bool;
    
    /**
     * Save addon with related data
     * 
     * @param Addon $addon
     * @param array $screenshots
     * @param array $tagIds
     * @param array $uploads Screenshots and icon files
     * @return int
     */
    public function saveWithRelated(
        Addon $addon, 
        array $screenshots = [], 
        array $tagIds = [],
        array $uploads = []
    ): int;
    
    /**
     * Get addon with related data
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithRelated(int $id): ?array;
    
    /**
     * Find similar addons
     * 
     * @param int $addonId
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findSimilarAddons(int $addonId, int $limit = 5): Collection;
    
    /**
     * Advanced search
     * 
     * @param string $query
     * @param array $fields
     * @param array $filters
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function advancedSearch(
        string $query, 
        array $fields = ['name', 'description'], 
        array $filters = [], 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection;
}