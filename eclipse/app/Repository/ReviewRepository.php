<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\AddonReview;
use Nette\Database\Explorer;

class ReviewRepository extends BaseRepository
{
    /** @var AddonRepository */
    private AddonRepository $addonRepository;

    public function __construct(Explorer $database, AddonRepository $addonRepository)
    {
        parent::__construct($database);
        $this->tableName = 'addon_reviews';
        $this->entityClass = AddonReview::class;
        $this->addonRepository = $addonRepository;
    }

    /**
     * Create a new review
     * 
     * @param AddonReview $review
     * @return int
     */
    public function create(AddonReview $review): int
    {
        // Set timestamp
        $review->created_at = new \DateTime();
        
        // Insert the review
        $reviewId = $this->save($review);
        
        // Update addon rating
        $this->addonRepository->updateRating($review->addon_id);
        
        return $reviewId;
    }

    /**
     * Delete a review
     * 
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        // Get the addon ID first
        $review = $this->findById($id);
        $addonId = $review ? $review->addon_id : null;
        
        // Delete the review
        $result = parent::delete($id);
        
        // Update addon rating
        if ($addonId) {
            $this->addonRepository->updateRating($addonId);
        }
        
        return $result;
    }

    /**
     * Find reviews by addon
     * 
     * @param int $addonId
     * @return array
     */
    public function findByAddon(int $addonId): array
    {
        $rows = $this->findBy(['addon_id' => $addonId])
            ->order('created_at DESC');
        
        $reviews = [];
        foreach ($rows as $row) {
            $reviews[] = AddonReview::fromArray($row->toArray());
        }
        
        return $reviews;
    }
}