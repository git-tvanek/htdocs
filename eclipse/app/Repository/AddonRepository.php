<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Addon;
use App\Model\Screenshot;
use App\Model\Tag;
use App\Model\AddonTag;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\Utils\Strings;

class AddonRepository extends BaseRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'addons';
        $this->entityClass = Addon::class;
    }

    /**
     * Find addon by slug
     * 
     * @param string $slug
     * @return Addon|null
     */
    public function findBySlug(string $slug): ?Addon
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Find addons by category
     * 
     * @param int $categoryId
     * @param int $page
     * @param int $itemsPerPage
     * @return array
     */
    public function findByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): array
    {
        return $this->findWithPagination(['category_id' => $categoryId], $page, $itemsPerPage, 'name', 'ASC');
    }

    /**
     * Find addons by author
     * 
     * @param int $authorId
     * @param int $page
     * @param int $itemsPerPage
     * @return array
     */
    public function findByAuthor(int $authorId, int $page = 1, int $itemsPerPage = 10): array
    {
        return $this->findWithPagination(['author_id' => $authorId], $page, $itemsPerPage, 'name', 'ASC');
    }

    /**
     * Find popular addons
     * 
     * @param int $limit
     * @return Addon[]
     */
    public function findPopular(int $limit = 10): array
    {
        $rows = $this->findAll()->order('downloads_count DESC')->limit($limit);
        $addons = [];
        
        foreach ($rows as $row) {
            $addons[] = Addon::fromArray($row->toArray());
        }
        
        return $addons;
    }

    /**
     * Find top rated addons
     * 
     * @param int $limit
     * @return Addon[]
     */
    public function findTopRated(int $limit = 10): array
    {
        $rows = $this->findAll()->order('rating DESC')->limit($limit);
        $addons = [];
        
        foreach ($rows as $row) {
            $addons[] = Addon::fromArray($row->toArray());
        }
        
        return $addons;
    }

    /**
     * Find newest addons
     * 
     * @param int $limit
     * @return Addon[]
     */
    public function findNewest(int $limit = 10): array
    {
        $rows = $this->findAll()->order('created_at DESC')->limit($limit);
        $addons = [];
        
        foreach ($rows as $row) {
            $addons[] = Addon::fromArray($row->toArray());
        }
        
        return $addons;
    }

    /**
     * Search addons by keyword
     * 
     * @param string $query
     * @param int $page
     * @param int $itemsPerPage
     * @return array
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): array
    {
        $selection = $this->getTable()
            ->where('name LIKE ? OR description LIKE ?', 
                "%{$query}%", "%{$query}%");
        
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        $items = [];
        foreach ($selection as $row) {
            $items[] = Addon::fromArray($row->toArray());
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
     * Increment addon download count
     * 
     * @param int $id
     * @return int
     */
    public function incrementDownloadCount(int $id): int
    {
        return $this->getTable()->where('id', $id)->update([
            'downloads_count' => new \Nette\Database\SqlLiteral('downloads_count + 1')
        ]);
    }

    /**
     * Update addon rating
     * 
     * @param int $id
     */
    public function updateRating(int $id): void
    {
        $averageRating = $this->database->table('addon_reviews')
            ->where('addon_id', $id)
            ->select('AVG(rating) AS average_rating')
            ->fetch();

        if ($averageRating && $averageRating->average_rating) {
            $addon = $this->findById($id);
            if ($addon) {
                $addon->rating = (float) $averageRating->average_rating;
                $this->save($addon);
            }
        }
    }

    /**
     * Create addon with related data
     * 
     * @param Addon $addon
     * @param array $screenshots
     * @param array $tagIds
     * @return int
     */
    public function createWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int
    {
        // Generate slug if not provided
        if (empty($addon->slug)) {
            $addon->slug = Strings::webalize($addon->name);
        }
        
        // Set timestamps
        $addon->created_at = new \DateTime();
        $addon->updated_at = new \DateTime();
        
        // Begin transaction
        $this->database->beginTransaction();
        
        try {
            // Insert addon
            $addonId = $this->save($addon);
            
            // Insert screenshots
            if (!empty($screenshots)) {
                foreach ($screenshots as $index => $screenshot) {
                    $screenshot->addon_id = $addonId;
                    $screenshot->sort_order = $index;
                    $this->database->table('screenshots')->insert($screenshot->toArray());
                }
            }
            
            // Insert tags
            if (!empty($tagIds)) {
                foreach ($tagIds as $tagId) {
                    $addonTag = new AddonTag();
                    $addonTag->addon_id = $addonId;
                    $addonTag->tag_id = $tagId;
                    $this->database->table('addon_tags')->insert($addonTag->toArray());
                }
            }
            
            // Commit transaction
            $this->database->commit();
            
            return $addonId;
        } catch (\Exception $e) {
            // Rollback on error
            $this->database->rollBack();
            throw $e;
        }
    }

    /**
     * Update addon with related data
     * 
     * @param Addon $addon
     * @param array $screenshots
     * @param array $tagIds
     * @return bool
     */
    public function updateWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): bool
    {
        // Update slug if name changed
        if (empty($addon->slug)) {
            $addon->slug = Strings::webalize($addon->name);
        }
        
        // Set updated timestamp
        $addon->updated_at = new \DateTime();
        
        // Begin transaction
        $this->database->beginTransaction();
        
        try {
            // Update addon
            $this->save($addon);
            
            // Handle screenshots
            if (!empty($screenshots)) {
                // Remove existing screenshots
                $this->database->table('screenshots')->where('addon_id', $addon->id)->delete();
                
                // Add new screenshots
                foreach ($screenshots as $index => $screenshot) {
                    $screenshot->addon_id = $addon->id;
                    $screenshot->sort_order = $index;
                    $this->database->table('screenshots')->insert($screenshot->toArray());
                }
            }
            
            // Handle tags
            if (!empty($tagIds)) {
                // Remove existing tag associations
                $this->database->table('addon_tags')->where('addon_id', $addon->id)->delete();
                
                // Add new tag associations
                foreach ($tagIds as $tagId) {
                    $addonTag = new AddonTag();
                    $addonTag->addon_id = $addon->id;
                    $addonTag->tag_id = $tagId;
                    $this->database->table('addon_tags')->insert($addonTag->toArray());
                }
            }
            
            // Commit transaction
            $this->database->commit();
            
            return true;
        } catch (\Exception $e) {
            // Rollback on error
            $this->database->rollBack();
            throw $e;
        }
    }
    
    /**
     * Get addon with related data
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithRelated(int $id): ?array
    {
        $addon = $this->findById($id);
        
        if (!$addon) {
            return null;
        }
        
        // Get author
        $authorRow = $this->database->table('authors')
            ->get($addon->author_id);
        $author = $authorRow ? \App\Model\Author::fromArray($authorRow->toArray()) : null;
        
        // Get category
        $categoryRow = $this->database->table('categories')
            ->get($addon->category_id);
        $category = $categoryRow ? \App\Model\Category::fromArray($categoryRow->toArray()) : null;
        
        // Get screenshots
        $screenshotRows = $this->database->table('screenshots')
            ->where('addon_id', $id)
            ->order('sort_order ASC');
        
        $screenshots = [];
        foreach ($screenshotRows as $screenshotRow) {
            $screenshots[] = \App\Model\Screenshot::fromArray($screenshotRow->toArray());
        }
        
        // Get tags
        $tagRows = $this->database->table('tags')
            ->select('tags.*')
            ->joinWhere('addon_tags', 'tags.id = addon_tags.tag_id')
            ->where('addon_tags.addon_id', $id);
        
        $tags = [];
        foreach ($tagRows as $tagRow) {
            $tags[] = \App\Model\Tag::fromArray($tagRow->toArray());
        }
        
        // Get reviews
        $reviewRows = $this->database->table('addon_reviews')
            ->where('addon_id', $id)
            ->order('created_at DESC');
        
        $reviews = [];
        foreach ($reviewRows as $reviewRow) {
            $reviews[] = \App\Model\AddonReview::fromArray($reviewRow->toArray());
        }
        
        return [
            'addon' => $addon,
            'author' => $author,
            'category' => $category,
            'screenshots' => $screenshots,
            'tags' => $tags,
            'reviews' => $reviews
        ];
    }
}