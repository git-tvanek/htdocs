<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\eclipse\app\Presentation/@layout.latte */
final class Template_08f7f5c096 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\eclipse\\app\\Presentation/@layout.latte';

	public const Blocks = [
		['head' => 'blockHead', 'scripts' => 'blockScripts'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		echo '<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>';
		if ($this->hasBlock('title')) /* line 6 */ {
			$this->renderBlock('title', [], function ($s, $type) {
				$ʟ_fi = new LR\FilterInfo($type);
				return LR\Filters::convertTo($ʟ_fi, 'html', $this->filters->filterContent('stripHtml', $ʟ_fi, $s));
			}) /* line 6 */;
			echo ' | ';
		}
		echo 'Kodi Addons Repository</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 15 */;
		echo '/css/style.css">
    
';
		$this->renderBlock('head', get_defined_vars()) /* line 17 */;
		echo '</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Home:')) /* line 23 */;
		echo '">
                <i class="fas fa-puzzle-piece me-2"></i>Kodi Addons
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link ';
		if ($presenter->getName() === 'Addon') /* line 33 */ {
			echo 'active';
		}
		echo '" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:')) /* line 33 */;
		echo '">
                            <i class="fas fa-cube me-1"></i> Addons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ';
		if ($presenter->getName() === 'Category') /* line 38 */ {
			echo 'active';
		}
		echo '" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:')) /* line 38 */;
		echo '">
                            <i class="fas fa-folder me-1"></i> Kategorie
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ';
		if ($presenter->getName() === 'Author') /* line 43 */ {
			echo 'active';
		}
		echo '" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:')) /* line 43 */;
		echo '">
                            <i class="fas fa-users me-1"></i> Autoři
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ';
		if ($presenter->getName() === 'Tag') /* line 48 */ {
			echo 'active';
		}
		echo '" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tag:')) /* line 48 */;
		echo '">
                            <i class="fas fa-tags me-1"></i> Tagy
                        </a>
                    </li>
                </ul>
                
                <form class="d-flex" action="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Search:')) /* line 54 */;
		echo '" method="get">
                    <input class="form-control me-2" type="search" name="query" placeholder="Hledat doplňky..." 
                           value="';
		echo LR\Filters::escapeHtmlAttr($presenter->getParameter('query')) /* line 56 */;
		echo '">
                    <button class="btn btn-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>
    
    <!-- Flash messages -->
    <div class="container mt-3">
';
		foreach ($flashes as $flash) /* line 67 */ {
			echo '        <div class="alert alert-';
			echo LR\Filters::escapeHtmlAttr($flash->type) /* line 67 */;
			echo ' alert-dismissible fade show">
            ';
			echo LR\Filters::escapeHtmlText($flash->message) /* line 68 */;
			echo '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
';

		}

		echo '    </div>
    
    <!-- Main content -->
    <main class="container py-4">
';
		$this->renderBlock('content', [], 'html') /* line 75 */;
		echo '    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>xAddons</h5>
                    <p>Objevujte a sdílejte nejlepší doplňky pro Kodi media center.</p>
                </div>
                <div class="col-md-3">
                    <h5>Rychlé odkazy</h5>
                    <ul class="list-unstyled">
                        <li><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Home:')) /* line 89 */;
		echo '" class="text-white">Domů</a></li>
                        <li><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:')) /* line 90 */;
		echo '" class="text-white">Doplňky</a></li>
                        <li><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:')) /* line 91 */;
		echo '" class="text-white">Kategorie</a></li>
                        <li><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Search:advanced')) /* line 92 */;
		echo '" class="text-white">Pokročilé vyhledávání</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Zdroje</h5>
                    <ul class="list-unstyled">
                        <li><a href="https://kodi.tv/" class="text-white" target="_blank">Oficiální stránka Kodi</a></li>
                        <li><a href="https://kodi.wiki/" class="text-white" target="_blank">Kodi Wiki</a></li>
                        <li><a href="#" class="text-white">Nápověda & Dokumentace</a></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; ';
		echo LR\Filters::escapeHtmlText(date('Y')) /* line 106 */;
		echo ' Kodi Addons Repository. Všechna práva vyhrazena.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
';
		$this->renderBlock('scripts', get_defined_vars()) /* line 114 */;
		echo '</body>
</html>';
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['flash' => '67'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block head} on line 17 */
	public function blockHead(array $ʟ_args): void
	{
	}


	/** {block scripts} on line 114 */
	public function blockScripts(array $ʟ_args): void
	{
	}
}
