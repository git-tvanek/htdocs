<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\eclipse\app\Presentation\Category/add.latte */
final class Template_ede52fc3c3 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\eclipse\\app\\Presentation\\Category/add.latte';

	public const Blocks = [
		['content' => 'blockContent'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		$this->renderBlock('content', get_defined_vars()) /* line 1 */;
	}


	/** {block content} on line 1 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<h1>Přidat kategorii</h1>

';
		$form = $this->global->formsStack[] = $this->global->uiControl['categoryForm'] /* line 4 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* line 4 */;
		echo '>
    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getLabelPart())->attributes() /* line 6 */;
		echo '>Název kategorie</label>
        <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 7 */;
		echo ' class="form-control">
    </div>
    
    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('description', $this->global)->getLabelPart())->attributes() /* line 11 */;
		echo '>Popis</label>
        <textarea';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('description', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 12 */;
		echo ' class="form-control">';
		echo $ʟ_elem->getHtml() /* line 12 */;
		echo '</textarea>
    </div>
    
    <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('save', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 15 */;
		echo ' class="btn btn-primary">Uložit</button>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* line 4 */;
		echo '</form>
';
		array_pop($this->global->formsStack);
	}
}
