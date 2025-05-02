<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Author;
use App\Collection\PaginatedCollection;

/**
 * Interface for author service
 */
interface IAuthorService extends IBaseService
{
    /**
     * Get author with addons
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithAddons(int $id): ?array;
    
    /**
     * Find authors with filters
     * 
     * @param array $filters
     * @param string $sortBy
     * @param string $sortDir
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Author>
     */
    public function findWithFilters(
        array $filters = [], 
        string $sortBy = 'name', 
        string $sortDir = 'ASC', 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection;
    
    /**
     * Get author statistics
     * 
     * @param int $authorId
     * @return array
     */
    public function getAuthorStatistics(int $authorId): array;
    
    /**
     * Get collaboration network
     * 
     * @param int $authorId
     * @param int $depth
     * @return array
     */
    public function getCollaborationNetwork(int $authorId, int $depth = 2): array;
    
    /**
     * Get top authors by metric
     * 
     * @param string $metric
     * @param int $limit
     * @return array
     */
    public function getTopAuthors(string $metric = 'addons', int $limit = 10): array;
}