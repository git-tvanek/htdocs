<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Tag;
use App\Model\Addon;
use Nette\Database\Explorer;
use Nette\Utils\Strings;

class TagRepository extends BaseRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'tags';
        $this->entityClass = Tag::class;
    }

    /**
     * Find tag by slug
     * 
     * @param string $slug
     * @return Tag|null
     */
    public function findBySlug(string $slug): ?Tag
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Find or create a tag
     * 
     * @param string $name
     * @return int
     */
    public function findOrCreate(string $name): int
    {
        $slug = Strings::webalize($name);
        $tag = $this->findBySlug($slug);
        
        if ($tag) {
            return $tag->id;
        }
        
        $newTag = new Tag();
        $newTag->name = $name;
        $newTag->slug = $slug;
        
        return $this->save($newTag);
    }

    /**
     * Create a new tag
     * 
     * @param Tag $tag
     * @return int
     */
    public function create(Tag $tag): int
    {
        // Generate slug if not provided
        if (empty($tag->slug)) {
            $tag->slug = Strings::webalize($tag->name);
        }
        
        return $this->save($tag);
    }

    /**
     * Get tags with their addon counts
     * 
     * @return array
     */
    public function getTagsWithCounts(): array
{
    $result = $this->database->query('
        SELECT t.*, COUNT(at.addon_id) AS addon_count
        FROM tags t
        LEFT JOIN addon_tags at ON t.id = at.tag_id
        GROUP BY t.id
        ORDER BY t.name ASC
    ');
         
    $tags = [];
    foreach ($result as $row) {
        $tag = Tag::fromArray((array)$row);
        $tagData = $tag->toArray();
        $tagData['addon_count'] = $row->addon_count;
        $tags[] = $tagData;
    }
         
    return $tags;
}

    /**
     * Find addons by tag
     * 
     * @param int $tagId
     * @param int $page
     * @param int $itemsPerPage
     * @return array
     */
    public function findAddonsByTag(int $tagId, int $page = 1, int $itemsPerPage = 10): array
    {
        $selection = $this->database->table('addons')
            ->select('addons.*')
            ->joinWhere('addon_tags', 'addons.id = addon_tags.addon_id')
            ->where('addon_tags.tag_id', $tagId)
            ->order('addons.name ASC');
        
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
}