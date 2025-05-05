<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\eclipse\app\Presentation\Search/default.latte */
final class Template_e865aac486 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\eclipse\\app\\Presentation\\Search/default.latte';

	public const Blocks = [
		['title' => 'blockTitle', 'content' => 'blockContent'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		$this->renderBlock('title', get_defined_vars()) /* line 1 */;
		echo '

';
		$this->renderBlock('content', get_defined_vars()) /* line 3 */;
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['addon' => '36', 'tag' => '126', 'author' => '144'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block title} on line 1 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Vyhledávání';
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="mb-4">
    <h1>Výsledky vyhledávání</h1>
    
    <form action="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this')) /* line 7 */;
		echo '" method="get" class="mt-3">
        <div class="input-group input-group-lg">
            <input type="text" class="form-control" name="query" value="';
		echo LR\Filters::escapeHtmlAttr($query) /* line 9 */;
		echo '" placeholder="Hledat doplňky...">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search me-1"></i> Hledat
            </button>
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Search:advanced')) /* line 13 */;
		echo '" class="btn btn-outline-secondary">
                <i class="fas fa-sliders-h me-1"></i> Pokročilé vyhledávání
            </a>
        </div>
    </form>
</div>

';
		if ($query) /* line 20 */ {
			echo '    <div class="alert alert-info">
        <i class="fas fa-search me-1"></i> Výsledky vyhledávání pro: <strong>';
			echo LR\Filters::escapeHtmlText($query) /* line 22 */;
			echo '</strong>
    </div>
';
		}
		echo '
<div class="row">
    <div class="col-md-9">
        <!-- Addon Results -->
';
		if (isset($results['addons']) && $results['addons']->getItems()->count() > 0) /* line 29 */ {
			echo '            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Nalezené doplňky (';
			echo LR\Filters::escapeHtmlText($results['addons']->getTotalCount()) /* line 32 */;
			echo ')</h5>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-3 g-4">
';
			foreach ($results['addons']->getItems() as $addon) /* line 36 */ {
				echo '                            <div class="col">
                                <div class="card h-100 hover-shadow">
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0" style="width: 50px; height: 50px;">
';
				if ($addon->icon_url) /* line 42 */ {
					echo '                                                    <img src="';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 43 */;
					echo '/uploads/';
					echo LR\Filters::escapeHtmlAttr($addon->icon_url) /* line 43 */;
					echo '" class="img-fluid rounded" alt="';
					echo LR\Filters::escapeHtmlAttr($addon->name) /* line 43 */;
					echo '">
';
				} else /* line 44 */ {
					echo '                                                    <div class="d-flex justify-content-center align-items-center h-100 bg-light rounded">
                                                        <i class="fas fa-cube text-secondary"></i>
                                                    </div>
';
				}
				echo '                                            </div>
                                            <div class="ms-3">
                                                <h5 class="card-title mb-1">
                                                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:detail', [$addon->slug])) /* line 52 */;
				echo '" class="text-decoration-none">';
				echo LR\Filters::escapeHtmlText($addon->name) /* line 52 */;
				echo '</a>
                                                </h5>
                                                <div class="mb-2">
';
				for ($i = 1;
				$i <= 5;
				$i++) /* line 55 */ {
					echo '                                                        <i class="fas fa-star ';
					if ($i <= $addon->rating) /* line 56 */ {
						echo 'text-warning';
					} else /* line 56 */ {
						echo 'text-muted';
					}
					echo '"></i>
';

				}
				echo '                                                </div>
                                                <p class="card-text small mb-0 text-muted">
                                                    <i class="fas fa-download me-1"></i> ';
				echo LR\Filters::escapeHtmlText($addon->downloads_count) /* line 60 */;
				echo ' stažení
                                                </p>
                                            </div>
                                        </div>
                                        
';
				if ($addon->description) /* line 65 */ {
					echo '                                            <p class="card-text small mt-3">
                                                ';
					echo LR\Filters::escapeHtmlText(($this->filters->truncate)($addon->description, 100)) /* line 67 */;
					echo '
                                            </p>
';
				}
				echo '                                    </div>
                                    <div class="card-footer bg-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Verze: ';
				echo LR\Filters::escapeHtmlText($addon->version) /* line 73 */;
				echo '</small>
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:download', [$addon->slug])) /* line 74 */;
				echo '" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i> Stáhnout
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
';

			}

			echo '                    </div>
                    
                    <!-- Pagination -->
';
			if ($results['addons']->getPages() > 1) /* line 85 */ {
				echo '                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item ';
				if (!$results['addons']->hasPreviousPage()) /* line 88 */ {
					echo 'disabled';
				}
				echo '">
                                    <a class="page-link" href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['query' => $query, 'page' => $results['addons']->getPreviousPage()])) /* line 89 */;
				echo '">&laquo; Předchozí</a>
                                </li>
                                
';
				for ($i = 1;
				$i <= $results['addons']->getPages();
				$i++) /* line 92 */ {
					echo '                                    <li class="page-item ';
					if ($i == $page) /* line 93 */ {
						echo 'active';
					}
					echo '">
                                        <a class="page-link" href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['query' => $query, 'page' => $i])) /* line 94 */;
					echo '">';
					echo LR\Filters::escapeHtmlText($i) /* line 94 */;
					echo '</a>
                                    </li>
';

				}
				echo '                                
                                <li class="page-item ';
				if (!$results['addons']->hasNextPage()) /* line 98 */ {
					echo 'disabled';
				}
				echo '">
                                    <a class="page-link" href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['query' => $query, 'page' => $results['addons']->getNextPage()])) /* line 99 */;
				echo '">Další &raquo;</a>
                                </li>
                            </ul>
                        </nav>
';
			}
			echo '                </div>
            </div>
';
		} elseif ($query) /* line 106 */ {
			echo '            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-1"></i> Nebyly nalezeny žádné doplňky odpovídající vašemu dotazu. Zkuste jiné klíčové slovo nebo <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:add')) /* line 108 */;
			echo '" class="alert-link">přidejte nový doplněk</a>.
            </div>
';
		} else /* line 110 */ {
			echo '            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i> Zadejte hledaný výraz pro vyhledávání doplňků.
            </div>
';
		}

		echo '    </div>
    
    <div class="col-md-3">
        <!-- Related Tags -->
';
		if (isset($results['tags']) && count($results['tags']) > 0) /* line 119 */ {
			echo '            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Související tagy</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
';
			foreach ($results['tags'] as $tag) /* line 126 */ {
				echo '                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tag:detail', [$tag->slug])) /* line 127 */;
				echo '" class="btn btn-sm btn-outline-secondary">
                                ';
				echo LR\Filters::escapeHtmlText($tag->name) /* line 128 */;
				echo '
                            </a>
';

			}

			echo '                    </div>
                </div>
            </div>
';
		}
		echo '        
        <!-- Related Authors -->
';
		if (isset($results['authors']) && count($results['authors']) > 0) /* line 137 */ {
			echo '            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Související autoři</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
';
			foreach ($results['authors'] as $author) /* line 144 */ {
				echo '                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:detail', [$author->id])) /* line 145 */;
				echo '" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
                                    <div>
                                        <h6 class="mb-0">';
				echo LR\Filters::escapeHtmlText($author->name) /* line 149 */;
				echo '</h6>
';
				if ($author->website) /* line 150 */ {
					echo '                                            <small class="text-muted">';
					echo LR\Filters::escapeHtmlText(($this->filters->truncate)($author->website, 30)) /* line 151 */;
					echo '</small>
';
				}
				echo '                                    </div>
                                </div>
                            </a>
';

			}

			echo '                    </div>
                </div>
            </div>
';
		}
		echo '        
        <!-- Advanced Search Link -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Potřebujete více možností?</h5>
                <p class="card-text">Vyzkoušejte pokročilé vyhledávání s filtry podle kategorií, tagů, hodnocení a dalších kritérií.</p>
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Search:advanced', ['query' => $query])) /* line 167 */;
		echo '" class="btn btn-primary w-100">
                    <i class="fas fa-sliders-h me-1"></i> Pokročilé vyhledávání
                </a>
            </div>
        </div>
    </div>
</div>
';
	}
}
