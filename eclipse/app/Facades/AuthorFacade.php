<?php

declare(strict_types=1);

namespace App\Facades;

use App\Model\Author;
use App\Repository\AuthorRepository;
use App\Repository\AddonRepository;

class AuthorFacade
{
    private AuthorRepository $authorRepository;
    private AddonRepository $addonRepository;

    public function __construct(
        AuthorRepository $authorRepository,
        AddonRepository $addonRepository
    ) {
        $this->authorRepository = $authorRepository;
        $this->addonRepository = $addonRepository;
    }

    /**
     * Získání všech autorů
     */
    public function getAllAuthors(): array
    {
        $authors = [];
        foreach ($this->authorRepository->findAll() as $row) {
            $authors[] = Author::fromArray($row->toArray());
        }
        return $authors;
    }

    /**
     * Získání autora podle ID
     */
    public function getAuthorById(int $id): ?Author
    {
        return $this->authorRepository->findById($id);
    }

    /**
     * Získání autora s jeho doplňky
     */
    public function getAuthorWithAddons(int $id): ?array
    {
        return $this->authorRepository->getWithAddons($id);
    }

    /**
     * Vytvoření nového autora
     */
    public function createAuthor(array $data): int
    {
        $author = Author::fromArray($data);
        return $this->authorRepository->create($author);
    }

    /**
     * Aktualizace autora
     */
    public function updateAuthor(int $id, array $data): bool
    {
        $author = $this->authorRepository->findById($id);
        if (!$author) {
            return false;
        }
        
        // Aktualizace objektu autora
        foreach ($data as $key => $value) {
            if (property_exists($author, $key)) {
                $author->{$key} = $value;
            }
        }
        
        $this->authorRepository->save($author);
        return true;
    }

    /**
     * Smazání autora
     */
    public function deleteAuthor(int $id): bool
    {
        // Kontrola, zda autor nemá doplňky
        $addons = $this->addonRepository->findByAuthor($id, 1, 1);
        if ($addons['totalCount'] > 0) {
            throw new \RuntimeException('Autor má doplňky a nelze ho smazat');
        }
        
        return $this->authorRepository->delete($id);
    }

    /**
     * Sloučení dvou autorů
     */
    public function mergeAuthors(int $sourceId, int $targetId): bool
    {
        // Kontrola existence cílového autora
        $targetAuthor = $this->authorRepository->findById($targetId);
        if (!$targetAuthor) {
            throw new \InvalidArgumentException('Cílový autor neexistuje');
        }
        
        // Získání všech doplňků od zdrojového autora
        $page = 1;
        $itemsPerPage = 100;
        $allMoved = false;
        
        while (!$allMoved) {
            $addons = $this->addonRepository->findByAuthor($sourceId, $page, $itemsPerPage);
            
            if (empty($addons['items'])) {
                $allMoved = true;
                continue;
            }
            
            foreach ($addons['items'] as $addon) {
                $addon->author_id = $targetId;
                $this->addonRepository->save($addon);
            }
            
            $page++;
            
            if ($page > $addons['pages']) {
                $allMoved = true;
            }
        }
        
        // Smazání zdrojového autora
        return $this->authorRepository->delete($sourceId);
    }

    /**
     * Hledání autorů podle jména
     */
    public function searchAuthorsByName(string $name): array
    {
        return $this->authorRepository->findBy(['name LIKE ?' => "%{$name}%"]);
    }
}