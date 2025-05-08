<?php

declare(strict_types=1);

namespace App\Presentation\Addon;

use App\Presentation\BasePresenter;
use App\Facade\AddonFacade;
use App\Facade\CategoryFacade;
use App\Facade\AuthorFacade;
use App\Facade\TagFacade;
use App\Facade\ReviewFacade;
use App\Forms\Factory\AddonFormFactory;
use App\Forms\Factory\ReviewFormFactory;
use Nette\Application\UI\Form;
use Nette\Utils\Paginator;
use Nette\Utils\Image;

class AddonPresenter extends BasePresenter
{
    /** @var AddonFacade */
    private AddonFacade $addonFacade;
    
    /** @var CategoryFacade */
    private CategoryFacade $categoryFacade;
    
    /** @var AuthorFacade */
    private AuthorFacade $authorFacade;
    
    /** @var TagFacade */
    private TagFacade $tagFacade;
    
    /** @var ReviewFacade */
    private ReviewFacade $reviewFacade;
    
    /** @var AddonFormFactory */
    private AddonFormFactory $addonFormFactory;
    
    /** @var ReviewFormFactory */
    private ReviewFormFactory $reviewFormFactory;
    
    /** @var int|null */
    private ?int $userId = null;
    
    /** @var bool */
    private bool $userLoggedIn = false;
    
    /**
     * Constructor
     */
    public function __construct(
        AddonFacade $addonFacade,
        CategoryFacade $categoryFacade,
        AuthorFacade $authorFacade,
        TagFacade $tagFacade,
        ReviewFacade $reviewFacade,
        AddonFormFactory $addonFormFactory,
        ReviewFormFactory $reviewFormFactory
    ) {
        $this->addonFacade = $addonFacade;
        $this->categoryFacade = $categoryFacade;
        $this->authorFacade = $authorFacade;
        $this->tagFacade = $tagFacade;
        $this->reviewFacade = $reviewFacade;
        $this->addonFormFactory = $addonFormFactory;
        $this->reviewFormFactory = $reviewFormFactory;
        
        // In a real application, you would get these from a user service/authentication
        $this->userId = null;
        $this->userLoggedIn = false;
    }
    
    /**
     * Default action - list all add-ons
     */
    public function renderDefault(int $page = 1): void
    {
        $itemsPerPage = 12;
        
        // Get popular, top-rated, and newest add-ons
        $this->template->popularAddons = $this->addonFacade->getPopularAddons(6);
        $this->template->topRatedAddons = $this->addonFacade->getTopRatedAddons(6);
        $this->template->newestAddons = $this->addonFacade->getNewestAddons(6);
        
        // Get all add-ons with pagination
        $allAddons = $this->addonFacade->searchAddons('', [], $page, $itemsPerPage);
        
        $this->template->addons = $allAddons;
        $this->template->page = $page;
        
        // Get categories for filtering
        $this->template->categories = $this->categoryFacade->getRootCategories();
    }
    
    /**
     * Category action - list add-ons by category
     */
    public function renderCategory(string $slug, int $page = 1): void
    {
        $itemsPerPage = 12;
        
        // Get category by slug
        $category = $this->categoryFacade->getCategoryBySlug($slug);
        if (!$category) {
            $this->error('Category not found');
        }
        
        // Get add-ons in this category
        $addons = $this->addonFacade->searchAddons('', ['category_ids' => [$category->id]], $page, $itemsPerPage);
        
        // Get category path (breadcrumbs)
        $categoryPath = $this->categoryFacade->getCategoryPath($category->id);
        
        // Get subcategories
        $subcategories = $this->categoryFacade->getSubcategories($category->id);
        
        $this->template->category = $category;
        $this->template->categoryPath = $categoryPath;
        $this->template->subcategories = $subcategories;
        $this->template->addons = $addons;
        $this->template->page = $page;
    }
    
    /**
     * Detail action - show add-on detail
     */
    public function renderDetail(string $slug): void
    {
        // Get add-on by slug
        $addon = $this->addonFacade->getAddonDetail($slug);
        if (!$addon) {
            $this->error('Add-on not found');
        }
        
        $this->template->addon = $addon;
        
        // Check if the user has already reviewed this add-on (in a real app)
        $this->template->userHasReviewed = false;
        
        // Set title
        $this->template->title = $addon['addon']->name;
    }
    
    /**
     * Add action - form for adding a new add-on
     */
    public function renderAdd(): void
    {
        // Check if user is logged in (in a real app)
        // if (!$this->userLoggedIn) {
        //     $this->flashMessage('You must be logged in to add an add-on', 'danger');
        //     $this->redirect('Sign:in');
        // }
        
        $this->template->title = 'Add New Add-on';
    }
    
    /**
     * Edit action - form for editing an add-on
     */
    public function renderEdit(int $id): void
    {
        // Check if user is logged in (in a real app)
        // if (!$this->userLoggedIn) {
        //     $this->flashMessage('You must be logged in to edit an add-on', 'danger');
        //     $this->redirect('Sign:in');
        // }
        
        // Get add-on by ID
        $addon = $this->addonFacade->getAddonDetail($id);
        if (!$addon) {
            $this->error('Add-on not found');
        }
        
        // Check if user is the author (in a real app)
        // if ($addon['addon']->author_id !== $this->userId) {
        //     $this->flashMessage('You can only edit your own add-ons', 'danger');
        //     $this->redirect('Addon:detail', $addon['addon']->slug);
        // }
        
        $this->template->addon = $addon;
        $this->template->title = 'Edit Add-on: ' . $addon['addon']->name;
        
        // Set form defaults
        $this['addonForm']->setDefaults($addon['addon']->toArray());
    }

    /**
    * Delete action - delete an addon
     * 
    * @param int $id
    */
    public function renderDelete(int $id): void
    {
        // V reálné aplikaci by zde měla být kontrola přihlášení
        // if (!$this->getUser()->isLoggedIn()) {
        //     $this->flashMessage('Pro smazání doplňku musíte být přihlášen', 'danger');
        //     $this->redirect('Sign:in');
        // }
    
        try {
        // Smazání doplňku
            $result = $this->addonFacade->deleteAddon($id);
        
            if ($result) {
                $this->flashMessage('Doplněk byl úspěšně smazán', 'success');
            } else {
                $this->flashMessage('Doplněk se nepodařilo smazat', 'danger');
            }
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při mazání doplňku: ' . $e->getMessage(), 'danger');
        }
    
        // Přesměrování na seznam doplňků
        $this->redirect('Addon:default');
    }
    
    /**
     * Download action - increase download count and redirect to download URL
     */
    public function renderDownload(string $slug): void
    {
        // Get download URL and increment download count
        $downloadUrl = $this->addonFacade->getDownloadUrl($slug);
        if (!$downloadUrl) {
            $this->error('Add-on not found');
        }
        
        // Redirect to download URL
        $this->redirectUrl($downloadUrl);
    }
    
    /**
     * Component factory for the add-on form
     */
    protected function createComponentAddonForm(): Form
    {
        $form = $this->addonFormFactory->create();
        
        // Set callback for form processing
        $form->onSuccess[] = function (Form $form, array $data) {
            $this->processAddonForm($form, $data);
        };
        
        return $form->create();
    }
    
    /**
     * Process add-on form
     */
    private function processAddonForm(Form $form, array $data): void
    {
        $files = [];
        
        // Process uploaded files
        if ($form->getHttpData($form::DataFile, 'icon') && $form->getHttpData($form::DataFile, 'icon')->isOk()) {
            $files['icon'] = $form->getHttpData($form::DataFile, 'icon');
        }
        
        if ($form->getHttpData($form::DataFile, 'fanart') && $form->getHttpData($form::DataFile, 'fanart')->isOk()) {
            $files['fanart'] = $form->getHttpData($form::DataFile, 'fanart');
        }
        
        if ($form->getHttpData($form::DataFile, 'screenshots') && is_array($form->getHttpData($form::DataFile, 'screenshots'))) {
            $files['screenshots'] = $form->getHttpData($form::DataFile, 'screenshots');
        }
        
        // Get tags from input and process new tags
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);
        
        $newTags = $data['new_tags'] ?? '';
        unset($data['new_tags']);
        
        // Create or update add-on
        if (isset($data['id'])) {
            // Update existing add-on
            $addonId = $this->addonFacade->updateAddon($data['id'], $data, $files);
            $this->flashMessage('Add-on updated successfully', 'success');
        } else {
            // Create new add-on
            // In a real app, you would set the author ID to the current user
            $data['author_id'] = $data['author_id'] ?? 1;
            
            $addonId = $this->addonFacade->createAddon($data, $files);
            $this->flashMessage('Add-on created successfully', 'success');
        }
        
        // Get the updated add-on
        $addon = $this->addonFacade->getAddonDetail($addonId);
        
        // Redirect to add-on detail
        $this->redirect('Addon:detail', $addon['addon']->slug);
    }
    
    /**
     * Component factory for the review form
     */
    protected function createComponentReviewForm(): Form
    {
        // Get add-on ID from slug
        $addon = null;
        if ($this->getAction() === 'detail') {
            $addon = $this->addonFacade->getAddonDetail($this->getParameter('slug'));
            if ($addon) {
                $addon = $addon['addon'];
            }
        }
        
        $form = $this->reviewFormFactory->create($this->userLoggedIn, $this->userId);
        
        // Set callback for form processing
        $form->onSuccess[] = function (Form $form, array $data) {
            $this->processReviewForm($form, $data);
        };
        
        return $form->create($addon ? $addon->id : 0);
    }
    
    /**
     * Process review form
     */
    private function processReviewForm(Form $form, array $data): void
    {
        // Create review
        if ($this->userLoggedIn && $this->userId) {
            // Create review from logged-in user
            $this->reviewFacade->createUserReview(
                $data['addon_id'],
                $this->userId,
                $data['rating'],
                $data['comment'] ?? null
            );
        } else {
            // Create review from guest
            $this->reviewFacade->createGuestReview(
                $data['addon_id'],
                $data['name'],
                $data['email'] ?? null,
                $data['rating'],
                $data['comment'] ?? null
            );
        }
        
        // Get add-on detail
        $addon = $this->addonFacade->getAddonDetail($data['addon_id']);
        
        $this->flashMessage('Review added successfully', 'success');
        $this->redirect('Addon:detail', $addon['addon']->slug);
    }
}