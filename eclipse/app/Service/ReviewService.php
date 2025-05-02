<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\AddonReview;
use App\Repository\ReviewRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Review service implementation
 * 
 * @extends BaseService<AddonReview>
 * @implements IReviewService
 */
class ReviewService extends BaseService implements IReviewService
{
    /** @var ReviewRepository */
    private ReviewRepository $reviewRepository;
    
    /**
     * Constructor
     * 
     * @param ReviewRepository $reviewRepository
     */
    public function __construct(ReviewRepository $reviewRepository)
    {
        parent::__construct();
        $this->reviewRepository = $reviewRepository;
        $this->entityClass = AddonReview::class;
    }
    
    /**
     * Get repository for entity
     * 
     * @return ReviewRepository
     */
    protected function getRepository(): ReviewRepository
    {
        return $this->reviewRepository;
    }
    
    /**
     * Find reviews by addon
     * 
     * @param int $addonId
     * @return Collection<AddonReview>
     */
    public function findByAddon(int $addonId): Collection
    {
        return $this->reviewRepository->findByAddon($addonId);
    }
    
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
    ): PaginatedCollection {
        return $this->reviewRepository->findWithFilters(
            $filters, 
            $sortBy, 
            $sortDir, 
            $page, 
            $itemsPerPage
        );
    }
    
    /**
     * Get sentiment analysis
     * 
     * @param int $addonId
     * @return array
     */
    public function getSentimentAnalysis(int $addonId): array
    {
        return $this->reviewRepository->getSentimentAnalysis($addonId);
    }
    
    /**
     * Get review activity over time
     * 
     * @param int $addonId
     * @param string $interval
     * @param int $limit
     * @return array
     */
    public function getReviewActivityOverTime(int $addonId, string $interval = 'month', int $limit = 12): array
    {
        return $this->reviewRepository->getReviewActivityOverTime($addonId, $interval, $limit);
    }
    
    /**
     * Get most recent reviews
     * 
     * @param int $limit
     * @return array
     */
    public function getMostRecentReviews(int $limit = 10): array
    {
        return $this->reviewRepository->getMostRecentReviews($limit);
    }
    
    /**
     * Get reviews by rating
     * 
     * @param int $rating
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<AddonReview>
     */
    public function getReviewsByRating(int $rating, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->reviewRepository->getReviewsByRating($rating, $page, $itemsPerPage);
    }
    
    /**
     * Find common keywords in reviews
     * 
     * @param int $addonId
     * @param int $limit
     * @return array
     */
    public function findCommonKeywords(int $addonId, int $limit = 10): array
    {
        return $this->reviewRepository->findCommonKeywords($addonId, $limit);
    }
}