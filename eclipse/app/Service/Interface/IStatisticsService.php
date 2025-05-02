<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Interface for statistics service
 */
interface IStatisticsService
{
    /**
     * Get addon statistics over time
     * 
     * @param string $interval 'day', 'week', 'month', or 'year'
     * @param int $limit Number of intervals to return
     * @param string $metric 'downloads', 'ratings', or 'addons'
     * @return array
     */
    public function getAddonStatisticsOverTime(string $interval = 'month', int $limit = 12, string $metric = 'downloads'): array;
    
    /**
     * Get addon distribution by category
     * 
     * @return array
     */
    public function getAddonDistributionByCategory(): array;
    
    /**
     * Get rating distribution
     * 
     * @return array
     */
    public function getRatingDistribution(): array;
    
    /**
     * Get top authors by download count
     * 
     * @param int $limit
     * @return array
     */
    public function getTopAuthorsByDownloads(int $limit = 10): array;
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function getDashboardStatistics(): array;
}