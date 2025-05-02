<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\AddonReview;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Interface for review service
 */
interface IReviewService extends IBaseService
{
    /**
     * Find reviews by addon
     * 
     * @param int $addonId
     * @return Collection<AddonReview>
     */
    public function findByAddon(int $addonId): Collection;
    
    /**
     * Find reviews with filters
     * 
     * @param array $filters
     * @param string $sortBy
     * @param string $sortDir
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<AddonReview>
     */
    public function findWithFilters(
        array $filters = [], 
        string $sortBy = 'created_at', 
        string $sortDir = 'DESC', 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection;
    
    /**
     * Get sentiment analysis
     * 
     * @param int $addonId
     * @return array
     */
    public function getSentimentAnalysis(int $addonId): array;
    
    /**
     * Get review activity over time
     * 
     * @param int $addonId
     * @param string $interval
     * @param int $limit
     * @return array
     */
    public function getReviewActivityOverTime(int $addonId, string $interval = 'month', int $limit = 12): array;
    
    /**
     * Get most recent reviews
     * 
     * @param int $limit
     * @return array
     */
    public function getMostRecentReviews(int $limit = 10): array;
    
    /**
     * Get reviews by rating
     * 
     * @param int $rating
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<AddonReview>
     */
    public function getReviewsByRating(int $rating, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Find common keywords in reviews
     * 
     * @param int $addonId
     * @param int $limit
     * @return array
     */
    public function findCommonKeywords(int $addonId, int $limit = 10): array;
}