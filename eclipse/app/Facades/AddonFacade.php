<?php

declare(strict_types=1);

namespace App\Facades;

use App\Model\Addon;
use App\Model\Screenshot;
use App\Repository\AddonRepository;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Repository\AuthorRepository;
use App\Repository\ReviewRepository;
use Nette\Utils\Strings;

class AddonFacade
{
    private AddonRepository $addonRepository;
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;
    private AuthorRepository $authorRepository;
    private ReviewRepository $reviewRepository;

    public function __construct(
        AddonRepository $addonRepository,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        AuthorRepository $authorRepository,
        ReviewRepository $reviewRepository
    ) {
        $this->addonRepository = $addonRepository;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->authorRepository = $authorRepository;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Získání populárních doplňků pro domovskou stránku
     */
    public function getPopularAddons(int $limit = 10): array
    {
        return $this->addonRepository->findPopular($limit);
    }

    /**
     * Získání nejnovějších doplňků
     */
    public function getNewestAddons(int $limit = 10): array
    {
        return $this->addonRepository->findNewest($limit);
    }

    /**
     * Získání nejlépe hodnocených doplňků
     */
    public function getTopRatedAddons(int $limit = 10): array
    {
        return $this->addonRepository->findTopRated($limit);
    }

    /**
     * Získání detailu doplňku včetně souvisejících dat
     */
    public function getAddonDetail(int $id): ?array
    {
        return $this->addonRepository->getWithRelated($id);
    }

    /**
     * Získání doplňku podle slugu
     */
    public function getAddonBySlug(string $slug): ?array
    {
        $addon = $this->addonRepository->findBySlug($slug);
        if (!$addon) {
            return null;
        }
        
        return $this->addonRepository->getWithRelated($addon->id);
    }

    /**
     * Vytvoření nového doplňku
     */
    public function createAddon(array $data, array $screenshots = [], array $tags = []): int
    {
        // Zpracování autora
        $authorId = $this->processAuthor($data);
        $data['author_id'] = $authorId;
        
        // Zpracování kategorie
        if (isset($data['category']) && !isset($data['category_id'])) {
            $category = $this->categoryRepository->findBySlug($data['category']);
            if ($category) {
                $data['category_id'] = $category->id;
            }
        }
        
        // Generování slugu, pokud nebyl poskytnut
        if (empty($data['slug'])) {
            $data['slug'] = Strings::webalize($data['name']);
        }
        
        // Zpracování tagů
        $tagIds = $this->processTags($tags);
        
        // Vytvoření doplňku
        $addon = Addon::fromArray($data);
        
        // Zpracování screenshotů
        $screenshotObjects = [];
        foreach ($screenshots as $screenshot) {
            $screenshotObjects[] = Screenshot::fromArray($screenshot);
        }
        
        // Uložení doplňku a souvisejících dat
        return $this->addonRepository->createWithRelated($addon, $screenshotObjects, $tagIds);
    }

    /**
     * Aktualizace existujícího doplňku
     */
    public function updateAddon(int $id, array $data, array $screenshots = [], array $tags = []): bool
    {
        // Kontrola existence doplňku
        $addon = $this->addonRepository->findById($id);
        if (!$addon) {
            return false;
        }
        
        // Zpracování autora
        if (isset($data['author_name']) || isset($data['author_email']) || isset($data['author_website'])) {
            $authorId = $this->processAuthor($data);
            $data['author_id'] = $authorId;
        }
        
        // Zpracování kategorie
        if (isset($data['category']) && !isset($data['category_id'])) {
            $category = $this->categoryRepository->findBySlug($data['category']);
            if ($category) {
                $data['category_id'] = $category->id;
            }
        }
        
        // Generování slugu, pokud nebyl poskytnut
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Strings::webalize($data['name']);
        }
        
        // Zpracování tagů
        $tagIds = $this->processTags($tags);
        
        // Aktualizace objektu doplňku
        foreach ($data as $key => $value) {
            if (property_exists($addon, $key)) {
                $addon->{$key} = $value;
            }
        }
        
        // Zpracování screenshotů
        $screenshotObjects = [];
        foreach ($screenshots as $screenshot) {
            $screenshotObjects[] = Screenshot::fromArray($screenshot);
        }
        
        // Uložení aktualizovaného doplňku a souvisejících dat
        return $this->addonRepository->updateWithRelated($addon, $screenshotObjects, $tagIds);
    }

    /**
     * Smazání doplňku
     */
    public function deleteAddon(int $id): bool
    {
        return $this->addonRepository->delete($id);
    }

    /**
     * Zaznamenání stažení doplňku
     */
    public function incrementDownloadCount(int $id): void
    {
        $this->addonRepository->incrementDownloadCount($id);
    }

    /**
     * Přidání recenze k doplňku
     */
    public function addReview(array $data): int
    {
        $review = \App\Model\AddonReview::fromArray($data);
        return $this->reviewRepository->create($review);
    }

    /**
     * Vyhledávání doplňků
     */
    public function searchAddons(string $query, int $page = 1, int $itemsPerPage = 10): array
    {
        return $this->addonRepository->search($query, $page, $itemsPerPage);
    }

    /**
     * Zpracování autora - nalezení nebo vytvoření nového
     */
    private function processAuthor(array $data): int
    {
        // Pokud je zadáno ID autora, použijeme ho
        if (isset($data['author_id'])) {
            return (int) $data['author_id'];
        }
        
        // Sestavení dat autora
        $authorData = [
            'name' => $data['author_name'] ?? null,
            'email' => $data['author_email'] ?? null,
            'website' => $data['author_website'] ?? null,
        ];
        
        // Kontrola povinných údajů
        if (empty($authorData['name'])) {
            throw new \InvalidArgumentException('Jméno autora je povinné');
        }
        
        // Hledání autora podle jména a emailu
        $author = null;
        if (!empty($authorData['email'])) {
            $author = $this->authorRepository->findOneBy([
                'name' => $authorData['name'],
                'email' => $authorData['email']
            ]);
        } else {
            $author = $this->authorRepository->findOneBy(['name' => $authorData['name']]);
        }
        
        // Pokud autor existuje, vrátíme jeho ID
        if ($author) {
            return $author->id;
        }
        
        // Vytvoření nového autora
        $newAuthor = \App\Model\Author::fromArray($authorData);
        return $this->authorRepository->create($newAuthor);
    }

    /**
     * Zpracování tagů - nalezení nebo vytvoření nových
     */
    private function processTags(array $tags): array
    {
        $tagIds = [];
        
        foreach ($tags as $tag) {
            // Pokud je tag ID, přidáme ho přímo
            if (is_numeric($tag)) {
                $tagIds[] = (int) $tag;
                continue;
            }
            
            // Jinak vytvoříme nový tag nebo použijeme existující
            $tagIds[] = $this->tagRepository->findOrCreate($tag);
        }
        
        return $tagIds;
    }
}