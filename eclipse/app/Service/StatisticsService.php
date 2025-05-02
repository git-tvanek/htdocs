<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\AddonRepository;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use App\Repository\ReviewRepository;
use Nette\Database\Explorer;
use Nette\Utils\DateTime;

/**
 * Statistics service implementation
 */
class StatisticsService implements IStatisticsService
{
    /** @var AddonRepository */
    private AddonRepository $addonRepository;
    
    /** @var AuthorRepository */
    private AuthorRepository $authorRepository;
    
    /** @var CategoryRepository */
    private CategoryRepository $categoryRepository;
    
    /** @var ReviewRepository */
    private ReviewRepository $reviewRepository;
    
    /** @var Explorer */
    private Explorer $database;
    
    /**
     * Constructor
     * 
     * @param AddonRepository $addonRepository
     * @param AuthorRepository $authorRepository
     * @param CategoryRepository $categoryRepository
     * @param ReviewRepository $reviewRepository
     * @param Explorer $database
     */
    public function __construct(
        AddonRepository $addonRepository,
        AuthorRepository $authorRepository,
        CategoryRepository $categoryRepository,
        ReviewRepository $reviewRepository,
        Explorer $database
    ) {
        $this->addonRepository = $addonRepository;
        $this->authorRepository = $authorRepository;
        $this->categoryRepository = $categoryRepository;
        $this->reviewRepository = $reviewRepository;
        $this->database = $database;
    }
    
    /**
     * Get addon statistics over time
     * 
     * @param string $interval 'day', 'week', 'month', or 'year'
     * @param int $limit Number of intervals to return
     * @param string $metric 'downloads', 'ratings', or 'addons'
     * @return array
     */
    public function getAddonStatisticsOverTime(string $interval = 'month', int $limit = 12, string $metric = 'downloads'): array
    {
        return $this->addonRepository->getStatisticsOverTime($interval, $limit, $metric);
    }
    
    /**
     * Get addon distribution by category
     * 
     * @return array
     */
    public function getAddonDistributionByCategory(): array
    {
        return $this->addonRepository->getAddonDistributionByCategory();
    }
    
    /**
     * Get rating distribution
     * 
     * @return array
     */
    public function getRatingDistribution(): array
    {
        return $this->addonRepository->getRatingDistribution();
    }
    
    /**
     * Get top authors by download count
     * 
     * @param int $limit
     * @return array
     */
    public function getTopAuthorsByDownloads(int $limit = 10): array
    {
        return $this->addonRepository->getTopAuthorsByDownloads($limit);
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function getDashboardStatistics(): array
    {
        // Count total addons
        $totalAddons = $this->database->table('addons')->count();
        
        // Count total authors
        $totalAuthors = $this->database->table('authors')->count();
        
        // Count total categories
        $totalCategories = $this->database->table('categories')->count();
        
        // Count total reviews
        $totalReviews = $this->database->table('addon_reviews')->count();
        
        // Get average rating
        $avgRating = $this->database->table('addon_reviews')
            ->select('AVG(rating) AS avg_rating')
            ->fetch();
        
        // Get total downloads
        $totalDownloads = $this->database->table('addons')
            ->sum('downloads_count') ?? 0;
        
        // Get newest addons (last 30 days)
        $thirtyDaysAgo = (new DateTime())->modify('-30 days');
        $newAddonsCount = $this->database->table('addons')
            ->where('created_at >= ?', $thirtyDaysAgo->format('Y-m-d H:i:s'))
            ->count();
        
        // Get most popular categories
        $popularCategories = $this->categoryRepository->getMostPopularCategories(5);
        
        // Get recent reviews
        $recentReviews = $this->reviewRepository->getMostRecentReviews(5);
        
        return [
            'totalAddons' => $totalAddons,
            'totalAuthors' => $totalAuthors,
            'totalCategories' => $totalCategories,
            'totalReviews' => $totalReviews,
            'totalDownloads' => (int)$totalDownloads,
            'averageRating' => $avgRating ? round((float)$avgRating->avg_rating, 2) : 0,
            'newAddonsCount' => $newAddonsCount,
            'popularCategories' => $popularCategories,
            'recentReviews' => $recentReviews
        ];
    }
}