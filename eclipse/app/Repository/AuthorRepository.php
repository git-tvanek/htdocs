<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Author;
use App\Model\Addon;
use Nette\Database\Explorer;

class AuthorRepository extends BaseRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'authors';
        $this->entityClass = Author::class;
    }

    /**
     * Create a new author
     * 
     * @param Author $author
     * @return int
     */
    public function create(Author $author): int
    {
        // Set timestamps
        $author->created_at = new \DateTime();
        
        return $this->save($author);
    }

    /**
     * Find author with their addons
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithAddons(int $id): ?array
    {
        $author = $this->findById($id);
        
        if (!$author) {
            return null;
        }
        
        $addonRows = $this->database->table('addons')
            ->where('author_id', $id)
            ->order('name ASC');
        
        $addons = [];
        foreach ($addonRows as $row) {
            $addons[] = Addon::fromArray($row->toArray());
        }
        
        return [
            'author' => $author,
            'addons' => $addons
        ];
    }
}