<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\AddonRepository;
use App\Repository\AuthorRepository;
use App\Repository\TagRepository;

/**
 * Search service implementation
 */
class SearchService implements ISearchService
{
    /** @var AddonRepository */
    private AddonRepository $addonRepository;
    
    /** @var AuthorRepository */
    private AuthorRepository $authorRepository;
    
    /** @var TagRepository */
    private TagRepository $tagRepository;
    
    /**
     * Constructor
     * 
     * @param AddonRepository $addonRepository
     * @param AuthorRepository $authorRepository
     * @param TagRepository $tagRepository
     */
    public function __construct(
        AddonRepository $addonRepository,
        AuthorRepository $authorRepository,
        TagRepository $tagRepository
    ) {
        $this->addonRepository = $addonRepository;
        $this->authorRepository = $authorRepository;
        $this->tagRepository = $tagRepository;
    }
    
    /**
     * Perform a simple search
     * 
     * @param string $query
     * @param int $page
     * @param int $itemsPerPage
     * @return array
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): array
    {
        // Search for addons
        $addons = $this->addonRepository->search($query, $page, $itemsPerPage);
        
        // Find relevant tags
        $tags = $this->findRelevantTags($query, 5);
        
        // Find relevant authors
        $authors = $this->findRelevantAuthors($query, 3);
        
        return [
            'query' => $query,
            'addons' => $addons,
            'tags' => $tags,
            'authors' => $authors
        ];
    }
    
    /**
     * Perform an advanced search
     * 
     * @param string $query
     * @param array $filters
     * @param int $page
     * @param int $itemsPerPage
     * @return array
     */
    public function advancedSearch(
        string $query, 
        array $filters = [], 
        int $page = 1, 
        int $itemsPerPage = 10
    ): array {
        // Search for addons with advanced filtering
        $fields = ['name', 'description'];
        $addons = $this->addonRepository->advancedSearch($query, $fields, $filters, $page, $itemsPerPage);
        
        // Get possible filter options
        $filterOptions = $this->getFilterOptions();
        
        return [
            'query' => $query,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'addons' => $addons
        ];
    }
    
    /**
     * Find relevant tags based on query
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    private function findRelevantTags(string $query, int $limit): array
    {
        // Basic implementation to find tags by partial name match
        $tags = $this->tagRepository->findWithFilters(
            ['name' => $query],
            'name',
            'ASC',
            1,
            $limit
        );
        
        return $tags->getItems()->toArray();
    }
    
    /**
     * Find relevant authors based on query
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    private function findRelevantAuthors(string $query, int $limit): array
    {
        // Basic implementation to find authors by partial name match
        $authors = $this->authorRepository->findWithFilters(
            ['name' => $query],
            'name',
            'ASC',
            1,
            $limit
        );
        
        return $authors->getItems()->toArray();
    }
    
    /**
     * Get filter options for advanced search
     * 
     * @return array
     */
    private function getFilterOptions(): array
    {
        return [
            'categories' => $this->getCategoryOptions(),
            'tags' => $this->getTagOptions(),
            'ratings' => $this->getRatingOptions(),
            'sortOptions' => $this->getSortOptions()
        ];
    }
    
    /**
     * Get category options for filters
     * 
     * @return array
     */
    private function getCategoryOptions(): array
    {
        $categories = [];
        $rows = $this->addonRepository->getRepository()->getDatabase()->table('categories')
            ->order('name ASC');
        
        foreach ($rows as $row) {
            $categories[] = [
                'id' => $row->id,
                'name' => $row->name
            ];
        }
        
        return $categories;
    }
    
    /**
     * Get tag options for filters
     * 
     * @return array
     */
    private function getTagOptions(): array
    {
        $tagCounts = $this->tagRepository->getTagsWithCounts();
        return array_slice($tagCounts, 0, 20); // Return top 20 tags
    }
    
    /**
     * Get rating options for filters
     * 
     * @return array
     */
    private function getRatingOptions(): array
    {
        return [
            ['value' => 5, 'label' => '5 stars'],
            ['value' => 4, 'label' => '4+ stars'],
            ['value' => 3, 'label' => '3+ stars'],
            ['value' => 2, 'label' => '2+ stars'],
            ['value' => 1, 'label' => '1+ stars']
        ];
    }
    
    /**
     * Get sort options
     * 
     * @return array
     */
    private function getSortOptions(): array
    {
        return [
            ['field' => 'name', 'direction' => 'ASC', 'label' => 'Name (A-Z)'],
            ['field' => 'name', 'direction' => 'DESC', 'label' => 'Name (Z-A)'],
            ['field' => 'downloads_count', 'direction' => 'DESC', 'label' => 'Most Downloaded'],
            ['field' => 'rating', 'direction' => 'DESC', 'label' => 'Highest Rated'],
            ['field' => 'created_at', 'direction' => 'DESC', 'label' => 'Newest First'],
            ['field' => 'created_at', 'direction' => 'ASC', 'label' => 'Oldest First']
        ];
    }
}