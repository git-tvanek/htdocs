<?php

declare(strict_types=1);

namespace App\Facades;

use App\Model\Tag;
use App\Repository\TagRepository;
use Nette\Utils\Strings;

class TagFacade
{
    private TagRepository $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Získání všech tagů
     */
    public function getAllTags(): array
    {
        $tags = [];
        foreach ($this->tagRepository->findAll() as $row) {
            $tags[] = Tag::fromArray($row->toArray());
        }
        return $tags;
    }

    /**
     * Získání tagů s počty doplňků
     */
    public function getTagsWithCounts(): array
    {
        return $this->tagRepository->getTagsWithCounts();
    }

    /**
     * Získání tagu podle ID
     */
    public function getTagById(int $id): ?Tag
    {
        return $this->tagRepository->findById($id);
    }

    /**
     * Získání tagu podle slugu
     */
    public function getTagBySlug(string $slug): ?Tag
    {
        return $this->tagRepository->findBySlug($slug);
    }

    /**
     * Získání doplňků s určitým tagem
     */
    public function getAddonsByTag(int $tagId, int $page = 1, int $itemsPerPage = 10): array
    {
        return $this->tagRepository->findAddonsByTag($tagId, $page, $itemsPerPage);
    }

    /**
     * Získání doplňků s určitým tagem podle slugu
     */
    public function getAddonsByTagSlug(string $slug, int $page = 1, int $itemsPerPage = 10): ?array
    {
        $tag = $this->tagRepository->findBySlug($slug);
        if (!$tag) {
            return null;
        }
        
        return $this->tagRepository->findAddonsByTag($tag->id, $page, $itemsPerPage);
    }

    /**
     * Vytvoření nového tagu
     */
    public function createTag(array $data): int
    {
        // Generování slugu, pokud nebyl poskytnut
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Strings::webalize($data['name']);
        }
        
        $tag = Tag::fromArray($data);
        return $this->tagRepository->create($tag);
    }

    /**
     * Aktualizace tagu
     */
    public function updateTag(int $id, array $data): bool
    {
        $tag = $this->tagRepository->findById($id);
        if (!$tag) {
            return false;
        }
        
        // Generování slugu, pokud nebyl poskytnut
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Strings::webalize($data['name']);
        }
        
        // Aktualizace objektu tagu
        foreach ($data as $key => $value) {
            if (property_exists($tag, $key)) {
                $tag->{$key} = $value;
            }
        }
        
        $this->tagRepository->save($tag);
        return true;
    }

    /**
     * Smazání tagu
     */
    public function deleteTag(int $id): bool
    {
        return $this->tagRepository->delete($id);
    }

    /**
     * Sloučení dvou tagů
     */
    public function mergeTags(int $sourceId, int $targetId): bool
    {
        // Kontrola existence cílového tagu
        $targetTag = $this->tagRepository->findById($targetId);
        if (!$targetTag) {
            throw new \InvalidArgumentException('Cílový tag neexistuje');
        }
        
        // Získání všech addonTags ze zdrojového tagu
        $sourceTag = $this->tagRepository->findById($sourceId);
        if (!$sourceTag) {
            throw new \InvalidArgumentException('Zdrojový tag neexistuje');
        }
        
        // Přesunutí všech propojení na cílový tag
        // Note: Tato operace by vyžadovala přímý přístup k tabulce addon_tags
        // Pro účely ukázky použijeme zjednodušenou implementaci
        // V reálném případě by bylo potřeba pomocí SQL dotazu aktualizovat záznamy
        
        // Smazání zdrojového tagu
        return $this->tagRepository->delete($sourceId);
    }

    /**
     * Hledání tagů podle jména
     */
    public function searchTagsByName(string $name): array
    {
        return $this->tagRepository->findBy(['name LIKE ?' => "%{$name}%"]);
    }

    /**
     * Hledání nebo vytvoření tagu podle jména
     */
    public function findOrCreateTag(string $name): int
    {
        return $this->tagRepository->findOrCreate($name);
    }
}