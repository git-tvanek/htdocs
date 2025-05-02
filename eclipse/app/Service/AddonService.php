<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Addon;
use App\Model\Screenshot;
use App\Repository\AddonRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use Nette\Utils\Strings;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;

/**
 * Addon service implementation
 * 
 * @extends BaseService<Addon>
 * @implements IAddonService
 */
class AddonService extends BaseService implements IAddonService
{
    /** @var AddonRepository */
    private AddonRepository $addonRepository;
    
    /** @var string */
    private string $uploadsDir;
    
    /**
     * Constructor
     * 
     * @param AddonRepository $addonRepository
     * @param string $uploadsDir
     */
    public function __construct(
        AddonRepository $addonRepository,
        string $uploadsDir = 'uploads'
    ) {
        parent::__construct();
        $this->addonRepository = $addonRepository;
        $this->entityClass = Addon::class;
        $this->uploadsDir = $uploadsDir;
    }
    
    /**
     * Get repository for entity
     * 
     * @return AddonRepository
     */
    protected function getRepository(): AddonRepository
    {
        return $this->addonRepository;
    }
    
    /**
     * Find addon by slug
     * 
     * @param string $slug
     * @return Addon|null
     */
    public function findBySlug(string $slug): ?Addon
    {
        return $this->addonRepository->findBySlug($slug);
    }
    
    /**
     * Find addons by category
     * 
     * @param int $categoryId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->addonRepository->findByCategory($categoryId, $page, $itemsPerPage);
    }
    
    /**
     * Find addons by author
     * 
     * @param int $authorId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByAuthor(int $authorId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->addonRepository->findByAuthor($authorId, $page, $itemsPerPage);
    }
    
    /**
     * Find popular addons
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findPopular(int $limit = 10): Collection
    {
        return $this->addonRepository->findPopular($limit);
    }
    
    /**
     * Find top rated addons
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findTopRated(int $limit = 10): Collection
    {
        return $this->addonRepository->findTopRated($limit);
    }
    
    /**
     * Find newest addons
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findNewest(int $limit = 10): Collection
    {
        return $this->addonRepository->findNewest($limit);
    }
    
    /**
     * Search addons by keyword
     * 
     * @param string $query
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->addonRepository->search($query, $page, $itemsPerPage);
    }
    
/**
 * Increment download count
 * 
 * @param int $id
 * @return int Number of affected rows
 */
public function incrementDownloadCount(int $id): int
{
    return $this->addonRepository->incrementDownloadCount($id);
}
    
    /**
     * Save addon with related data
     * 
     * @param Addon $addon
     * @param array $screenshots
     * @param array $tagIds
     * @param array $uploads Screenshots and icon files
     * @return int
     * @throws \Exception
     */
    public function saveWithRelated(
        Addon $addon, 
        array $screenshots = [], 
        array $tagIds = [],
        array $uploads = []
    ): int {
        // Handle file uploads
        if (isset($uploads['icon']) && $uploads['icon'] instanceof FileUpload && $uploads['icon']->isOk()) {
            $iconPath = $this->processImageUpload($uploads['icon'], 'icons');
            $addon->icon_url = $iconPath;
        }
        
        if (isset($uploads['fanart']) && $uploads['fanart'] instanceof FileUpload && $uploads['fanart']->isOk()) {
            $fanartPath = $this->processImageUpload($uploads['fanart'], 'fanart');
            $addon->fanart_url = $fanartPath;
        }
        
        // Process screenshot uploads
        $processedScreenshots = [];
        if (!empty($uploads['screenshots'])) {
            foreach ($uploads['screenshots'] as $index => $screenshotUpload) {
                if ($screenshotUpload instanceof FileUpload && $screenshotUpload->isOk()) {
                    $screenshotPath = $this->processImageUpload($screenshotUpload, 'screenshots');
                    
                    $screenshot = new Screenshot();
                    $screenshot->url = $screenshotPath;
                    $screenshot->description = $screenshots[$index]['description'] ?? null;
                    $screenshot->sort_order = $index;
                    
                    $processedScreenshots[] = $screenshot;
                }
            }
        }
        
        // Ensure slug is set
        if (empty($addon->slug)) {
            $addon->slug = Strings::webalize($addon->name);
        }
        
        // Save to database
        if (isset($addon->id)) {
            return $this->addonRepository->updateWithRelated($addon, $processedScreenshots, $tagIds);
        } else {
            return $this->addonRepository->createWithRelated($addon, $processedScreenshots, $tagIds);
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
        return $this->addonRepository->getWithRelated($id);
    }
    
    /**
     * Find similar addons
     * 
     * @param int $addonId
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findSimilarAddons(int $addonId, int $limit = 5): Collection
    {
        return $this->addonRepository->findSimilarAddons($addonId, $limit);
    }
    
    /**
     * Advanced search
     * 
     * @param string $query
     * @param array $fields
     * @param array $filters
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function advancedSearch(
        string $query, 
        array $fields = ['name', 'description'], 
        array $filters = [], 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection {
        return $this->addonRepository->advancedSearch($query, $fields, $filters, $page, $itemsPerPage);
    }
    
    /**
     * Process image upload
     * 
     * @param FileUpload $file
     * @param string $subdir
     * @return string
     * @throws \Exception
     */
    private function processImageUpload(FileUpload $file, string $subdir): string
    {
        // Check if it's an image
        if (!$file->isImage()) {
            throw new \Exception('Uploaded file is not an image');
        }
        
        // Create target directory if it doesn't exist
        $dir = $this->uploadsDir . '/' . $subdir;
        if (!is_dir($dir)) {
            FileSystem::createDir($dir);
        }
        
        // Generate unique filename
        $ext = strtolower(pathinfo($file->getSanitizedName(), PATHINFO_EXTENSION));
        $filename = md5(uniqid('', true)) . '.' . $ext;
        $filepath = $dir . '/' . $filename;
        
        // Save file
        $file->move($filepath);
        
        // Return relative path for storage
        return $subdir . '/' . $filename;
    }
}