<?php

declare(strict_types=1);

namespace App\Service;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Base service implementation
 * 
 * @template T of object
 * @implements IBaseService<T>
 */
abstract class BaseService implements IBaseService
{
    /** @var string Entity class name */
    protected string $entityClass;
    
    /** 
     * Constructor
     */
    public function __construct()
    {
    }
    
    /**
     * Find an entity by ID
     * 
     * @param int $id
     * @return T|null
     */
    public function findById(int $id): ?object
    {
        return $this->getRepository()->findById($id);
    }
    
    /**
     * Find all entities
     * 
     * @return Collection<T>
     */
    public function findAll(): Collection
    {
        $results = $this->getRepository()->findAll();
        $entities = [];
        
        foreach ($results as $row) {
            $entities[] = call_user_func([$this->entityClass, 'fromArray'], $row->toArray());
        }
        
        return new Collection($entities);
    }
    
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
    ): PaginatedCollection {
        return $this->getRepository()->findWithPagination(
            $criteria, 
            $page, 
            $itemsPerPage, 
            $orderColumn, 
            $orderDir
        );
    }
    
    /**
     * Save an entity (create or update)
     * 
     * @param T $entity
     * @return int Entity ID
     */
    public function save(object $entity): int
    {
        return $this->getRepository()->save($entity);
    }
    
    /**
     * Delete an entity
     * 
     * @param int $id
     * @return bool Success
     */
    public function delete(int $id): bool
    {
        return $this->getRepository()->delete($id) > 0;
    }
    
    /**
     * Get repository for entity
     * 
     * @return App\Repository\BaseRepository<T>
     */
    abstract protected function getRepository();
}