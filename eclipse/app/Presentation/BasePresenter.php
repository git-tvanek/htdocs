<?php

declare(strict_types=1);

namespace App\Presentation;

use Nette;
use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter
{
    /**
     * Common initialization for all presenters
     */
    protected function startup(): void
    {
        parent::startup();
        // Common startup logic for all presenters
    }

    /**
     * Before render initialization for all presenters
     */
    protected function beforeRender(): void
    {
        parent::beforeRender();
        
        // Set common template variables
        $this->template->menuActive = $this->getName();
    }
    
    /**
     * Create flash message
     * 
     * @param string $message Message text
     * @param string $type Message type (success, info, warning, danger)
     */
    public function flashMessage(mixed $message, string $type = 'info'): \stdClass
    {
        return parent::flashMessage($message, $type);
    }
}