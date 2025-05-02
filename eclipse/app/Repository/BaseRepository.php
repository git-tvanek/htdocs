<?php

declare(strict_types=1);

namespace App\Repository;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\SmartObject;

abstract class BaseRepository
{
    use SmartObject;

    /** @var Explorer */
    protected Explorer $database;

    /** @var string */
    protected string $tableName;

    /** @var string The entity class name this repository manages */
    protected string $entityClass;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Get all records
     * 
     * @return Selection
     */
    public function findAll(): Selection
    {
        return $this->getTable();
    }

    /**
     * Get record by ID
     * 
     * @param int $id
     * @return object|null The entity or null if not found
     */
    public function findById(int $id): ?object
    {
        $row = $this->getTable()->get($id);
        return $row ? $this->createEntity($row->toArray()) : null;
    }

    /**
     * Find one record by given criteria
     * 
     * @param array $criteria
     * @return object|null The entity or null if not found
     */
    public function findOneBy(array $criteria): ?object
    {
        $result = $this->findBy($criteria)->limit(1)->fetch();
        return $result ? $this->createEntity($result->toArray()) : null;
    }

    /**
     * Find records by given criteria
     * 
     * @param array $criteria
     * @return Selection
     */
    public function findBy(array $criteria = []): Selection
    {
        $selection = $this->getTable();
        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                $selection->where($key, $value);
            } else {
                $selection->where($key, $value);
            }
        }
        return $selection;
    }

    /**
     * Create a new record
     * 
     * @param object $entity
     * @return int The ID of the new record
     */
    public function save(object $entity): int
    {
        $data = $this->entityToArray($entity);
        
        // Remove ID for insertions
        if (!isset($entity->id)) {
            unset($data['id']);
        }
        
        if (isset($entity->id)) {
            // Update existing record
            $this->getTable()->wherePrimary($entity->id)->update($data);
            return $entity->id;
        } else {
            // Insert new record
            $row = $this->getTable()->insert($data);
            $id = $row->getPrimary();
            $entity->id = $id;
            return $id;
        }
    }

    /**
     * Delete a record
     * 
     * @param int $id
     * @return int Number of affected rows
     */
    public function delete(int $id): int
    {
        return $this->getTable()->wherePrimary($id)->delete();
    }

    /**
     * Count records based on criteria
     * 
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = []): int
    {
        return $this->findBy($criteria)->count();
    }

    /**
     * Find records with pagination
     * 
     * @param array $criteria
     * @param int $page
     * @param int $itemsPerPage
     * @param string $orderColumn
     * @param string $orderDir
     * @return array
     */
    public function findWithPagination(array $criteria = [], int $page = 1, int $itemsPerPage = 10, string $orderColumn = 'id', string $orderDir = 'ASC'): array
    {
        $selection = $this->findBy($criteria);
        $selection->order("$orderColumn $orderDir");
        
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Convert rows to entities
        $items = [];
        foreach ($selection as $row) {
            $items[] = $this->createEntity($row->toArray());
        }
        
        return [
            'items' => $items,
            'totalCount' => $count,
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'pages' => $pages
        ];
    }

    /**
     * Get the table
     * 
     * @return Selection
     */
    protected function getTable(): Selection
    {
        return $this->database->table($this->tableName);
    }
    
    /**
     * Create an entity instance from data array
     * 
     * @param array $data
     * @return object
     */
    protected function createEntity(array $data): object
    {
        return call_user_func([$this->entityClass, 'fromArray'], $data);
    }

    /**
     * Convert entity to array for database operations
     * 
     * @param object $entity
     * @return array
     */
    protected function entityToArray(object $entity): array
    {
        return $entity->toArray();
    }
}