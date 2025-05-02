<?php

declare(strict_types=1);

namespace App\Facades;

use App\Repository\AddonRepository;
use App\Repository\CategoryRepository;
use App\Repository\AuthorRepository;
use App\Repository\TagRepository;
use App\Repository\ReviewRepository;

class StatisticsFacade
{
    private AddonRepository $addonRepository;
    private CategoryRepository $categoryRepository;
    private AuthorRepository $authorRepository;
    private TagRepository $tagRepository;
    private ReviewRepository $reviewRepository;

    public function __construct(
        AddonRepository $addonRepository,
        CategoryRepository $categoryRepository,
        AuthorRepository $authorRepository,
        TagRepository $tagRepository,
        ReviewRepository $reviewRepository
    ) {
        $this->addonRepository = $addonRepository;
        $this->categoryRepository = $categoryRepository;
        $this->authorRepository = $authorRepository;
        $this->tagRepository = $tagRepository;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Získání souhrnných statistik pro dashboard
     */
    public function getDashboardStats(): array
    {
        return [
            'totalAddons' => $this->addonRepository->count(),
            'totalCategories' => $this->categoryRepository->count(),
            'totalAuthors' => $this->authorRepository->count(),
            'totalTags' => $this->tagRepository->count(),
            'popularAddons' => $this->addonRepository->findPopular(5),
            'newestAddons' => $this->addonRepository->findNewest(5),
            'topRatedAddons' => $this->addonRepository->findTopRated(5)
        ];
    }

    /**
     * Získání statistik stažení podle kategorie
     */
    public function getDownloadsByCategory(): array
    {
        $categories = $this->categoryRepository->findAll();
        $stats = [];
        
        foreach ($categories as $category) {
            $addons = $this->addonRepository->findByCategory($category->id, 1, 1000);
            
            $totalDownloads = 0;
            foreach ($addons['items'] as $addon) {
                $totalDownloads += $addon->downloads_count;
            }
            
            $stats[] = [
                'category' => $category->name,
                'downloads' => $totalDownloads,
                'addonsCount' => $addons['totalCount']
            ];
        }
        
        // Seřazení podle počtu stažení
        usort($stats, function($a, $b) {
            return $b['downloads'] <=> $a['downloads'];
        });
        
        return $stats;
    }

    /**
     * Získání statistik hodnocení podle kategorie
     */
    public function getRatingsByCategory(): array
    {
        $categories = $this->categoryRepository->findAll();
        $stats = [];
        
        foreach ($categories as $category) {
            $addons = $this->addonRepository->findByCategory($category->id, 1, 1000);
            
            $totalRating = 0;
            $ratedAddons = 0;
            
            foreach ($addons['items'] as $addon) {
                if ($addon->rating > 0) {
                    $totalRating += $addon->rating;
                    $ratedAddons++;
                }
            }
            
            $averageRating = $ratedAddons > 0 ? round($totalRating / $ratedAddons, 2) : 0;
            
            $stats[] = [
                'category' => $category->name,
                'averageRating' => $averageRating,
                'ratedAddonsCount' => $ratedAddons,
                'totalAddonsCount' => $addons['totalCount']
            ];
        }
        
        // Seřazení podle průměrného hodnocení
        usort($stats, function($a, $b) {
            return $b['averageRating'] <=> $a['averageRating'];
        });
        
        return $stats;
    }

    /**
     * Získání statistik pro nejaktivnější autory
     */
    public function getMostActiveAuthors(int $limit = 10): array
    {
        $authors = $this->authorRepository->findAll();
        $stats = [];
        
        foreach ($authors as $author) {
            $authorData = $this->authorRepository->getWithAddons($author->id);
            if (!$authorData) {
                continue;
            }
            
            $totalDownloads = 0;
            $averageRating = 0;
            $ratedAddons = 0;
            $addons = $authorData['addons'];
            
            foreach ($addons as $addon) {
                $totalDownloads += $addon->downloads_count;
                
                if ($addon->rating > 0) {
                    $averageRating += $addon->rating;
                    $ratedAddons++;
                }
            }
            
            $stats[] = [
                'author' => $author->name,
                'addonCount' => count($addons),
                'totalDownloads' => $totalDownloads,
                'averageRating' => $ratedAddons > 0 ? round($averageRating / $ratedAddons, 2) : 0
            ];
        }
        
        // Seřazení podle počtu doplňků
        usort($stats, function($a, $b) {
            return $b['addonCount'] <=> $a['addonCount'];
        });
        
        return array_slice($stats, 0, $limit);
    }

    /**
     * Získání statistik pro nejpopulárnější tagy
     */
    public function getMostPopularTags(int $limit = 10): array
    {
        $tags = $this->tagRepository->getTagsWithCounts();
        
        // Seřazení podle počtu doplňků
        usort($tags, function($a, $b) {
            return $b['addon_count'] <=> $a['addon_count'];
        });
        
        return array_slice($tags, 0, $limit);
    }

    /**
     * Získání dat pro graf vývoje v čase
     */
    public function getTimelineData(string $period = 'month', int $count = 12): array
    {
        // V reálné aplikaci byste museli implementovat složitější logiku
        // Pro zjednodušení vrátíme vymyšlená data
        
        $now = new \DateTime();
        $data = [];
        
        switch ($period) {
            case 'day':
                $interval = new \DateInterval('P1D');
                break;
            case 'week':
                $interval = new \DateInterval('P1W');
                break;
            case 'month':
                $interval = new \DateInterval('P1M');
                break;
            case 'year':
                $interval = new \DateInterval('P1Y');
                break;
            default:
                $interval = new \DateInterval('P1M');
        }
        
        $date = clone $now;
        for ($i = 0; $i < $count; $i++) {
            $date->sub($interval);
            
            // Formátování data
            switch ($period) {
                case 'day':
                    $label = $date->format('d.m.Y');
                    break;
                case 'week':
                    $label = $date->format('W/Y');
                    break;
                case 'month':
                    $label = $date->format('m/Y');
                    break;
                case 'year':
                    $label = $date->format('Y');
                    break;
                default:
                    $label = $date->format('m/Y');
            }
            
            // Vymyšlená data - v reálné aplikaci byste použili skutečná data z databáze
            $data[] = [
                'label' => $label,
                'addons' => rand(5, 20),
                'downloads' => rand(100, 1000),
                'reviews' => rand(2, 15)
            ];
        }
        
        // Seřazení od nejstaršího po nejnovější
        return array_reverse($data);
    }
}