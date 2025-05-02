<?php

declare(strict_types=1);

namespace App\Facades;

use App\Model\AddonReview;
use App\Repository\ReviewRepository;
use App\Repository\AddonRepository;

class ReviewFacade
{
    private ReviewRepository $reviewRepository;
    private AddonRepository $addonRepository;

    public function __construct(
        ReviewRepository $reviewRepository,
        AddonRepository $addonRepository
    ) {
        $this->reviewRepository = $reviewRepository;
        $this->addonRepository = $addonRepository;
    }

    /**
     * Získání recenzí pro doplněk
     */
    public function getReviewsForAddon(int $addonId): array
    {
        return $this->reviewRepository->findByAddon($addonId);
    }

    /**
     * Přidání recenze k doplňku
     */
    public function addReview(array $data): int
    {
        // Kontrola existence doplňku
        if (!isset($data['addon_id'])) {
            throw new \InvalidArgumentException('ID doplňku je povinné');
        }
        
        $addon = $this->addonRepository->findById((int)$data['addon_id']);
        if (!$addon) {
            throw new \InvalidArgumentException('Doplněk neexistuje');
        }
        
        // Vytvoření recenze
        $review = AddonReview::fromArray($data);
        
        // Uložení recenze
        $reviewId = $this->reviewRepository->create($review);
        
        return $reviewId;
    }

    /**
     * Smazání recenze
     */
    public function deleteReview(int $id): bool
    {
        return $this->reviewRepository->delete($id);
    }
      /**
     * Získání recenze podle ID
     */
    public function getReviewById(int $id): ?AddonReview
    {
        return $this->reviewRepository->findById($id);
    }

    /**
     * Aktualizace recenze
     */
    public function updateReview(int $id, array $data): bool
    {
        $review = $this->reviewRepository->findById($id);
        if (!$review) {
            return false;
        }
        
        // Aktualizace objektu recenze
        foreach ($data as $key => $value) {
            if (property_exists($review, $key)) {
                $review->{$key} = $value;
            }
        }
        
        // Uložení aktualizované recenze
        $this->reviewRepository->save($review);
        
        return true;
    }

    /**
     * Získání statistik recenzí pro doplněk
     */
    public function getReviewStatistics(int $addonId): array
    {
        $reviews = $this->getReviewsForAddon($addonId);
        
        if (empty($reviews)) {
            return [
                'count' => 0,
                'average' => 0,
                'distribution' => [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 0,
                ]
            ];
        }
        
        $totalRating = 0;
        $distribution = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        ];
        
        foreach ($reviews as $review) {
            $totalRating += $review->rating;
            $distribution[$review->rating]++;
        }
        
        return [
            'count' => count($reviews),
            'average' => count($reviews) > 0 ? round($totalRating / count($reviews), 2) : 0,
            'distribution' => $distribution
        ];
    }

    /**
     * Kontrola, zda uživatel již recenzoval doplněk
     */
    public function hasUserReviewed(int $addonId, ?int $userId = null, ?string $email = null): bool
    {
        if ($userId !== null) {
            $review = $this->reviewRepository->findOneBy([
                'addon_id' => $addonId,
                'user_id' => $userId
            ]);
            
            return $review !== null;
        }
        
        if ($email !== null) {
            $review = $this->reviewRepository->findOneBy([
                'addon_id' => $addonId,
                'email' => $email
            ]);
            
            return $review !== null;
        }
        
        return false;
    }
}