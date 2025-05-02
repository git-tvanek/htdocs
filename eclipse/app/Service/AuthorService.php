<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Author;
use App\Repository\AuthorRepository;
use App\Collection\PaginatedCollection;

/**
 * Author service implementation
 * 
 * @extends BaseService<Author>
 * @implements IAuthorService
 */
class AuthorService extends BaseService implements IAuthorService
{
    /** @var AuthorRepository */
    private AuthorRepository $authorRepository;
    
    /**
     * Constructor
     * 
     * @param AuthorRepository $authorRepository
     */
    public function __construct(AuthorRepository $authorRepository) 
    {
        parent::__construct();
        $this->authorRepository = $authorRepository;
        $this->entityClass = Author::class;
    }
    
    /**
     * Get repository for entity
     * 
     * @return AuthorRepository
     */
    protected function getRepository(): AuthorRepository
    {
        return $this->authorRepository;
    }
    
    /**
     * Get author with addons
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithAddons(int $id): ?array
    {
        return $this->authorRepository->getWithAddons($id);
    }
    
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
    ): PaginatedCollection {
        return $this->authorRepository->findWithFilters(
            $filters, 
            $sortBy, 
            $sortDir, 
            $page, 
            $itemsPerPage
        );
    }
    
    /**
     * Get author statistics
     * 
     * @param int $authorId
     * @return array
     */
    public function getAuthorStatistics(int $authorId): array
    {
        return $this->authorRepository->getAuthorStatistics($authorId);
    }
    
    /**
     * Get collaboration network
     * 
     * @param int $authorId
     * @param int $depth
     * @return array
     */
    public function getCollaborationNetwork(int $authorId, int $depth = 2): array
    {
        return $this->authorRepository->getCollaborationNetwork($authorId, $depth);
    }
    
    /**
     * Get top authors by metric
     * 
     * @param string $metric
     * @param int $limit
     * @return array
     */
    public function getTopAuthors(string $metric = 'addons', int $limit = 10): array
    {
        return $this->authorRepository->getTopAuthors($metric, $limit);
    }
}