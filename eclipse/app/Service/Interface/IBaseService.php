<?php

declare(strict_types=1);

namespace App\Service;

use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Base service interface
 * 
 * @template T
 */
interface IBaseService
{
    /**
     * Find an entity by ID
     * 
     * @param int $id
     * @return T|null
     */
    public function findById(int $id): ?object;
    
    /**
     * Find all entities
     * 
     * @return Collection<T>
     */
    public function findAll(): Collection;
    
    /**
     * Find entities with pagination
     * 
     * @param array $criteria
     * @param int $page
     * @param int $itemsPerPage
     * @param string $orderColumn
     * @param string $orderDir
     * @return PaginatedCollection<T>
     */
    public function findWithPagination(
        array $criteria = [], 
        int $page = 1, 
        int $itemsPerPage = 10, 
        string $orderColumn = 'id', 
        string $orderDir = 'ASC'
    ): PaginatedCollection;
    
    /**
     * Save an entity (create or update)
     * 
     * @param T $entity
     * @return int Entity ID
     */
    public function save(object $entity): int;
    
    /**
     * Delete an entity
     * 
     * @param int $id
     * @return bool Success
     */
    public function delete(int $id): bool;
}