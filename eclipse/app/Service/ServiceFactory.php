<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\AddonRepository;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use App\Repository\ReviewRepository;
use App\Repository\TagRepository;
use Nette\Database\Explorer;
use Nette\DI\Container;

/**
 * Service factory for registering services in DI container
 */
class ServiceFactory
{
    /**
     * Register services in DI container
     * 
     * @param Container $container
     */
    public static function registerServices(Container $container): void
    {
        // Register repositories
        $container->addService('addonRepository', new AddonRepository($container->getByType(Explorer::class)));
        $container->addService('authorRepository', new AuthorRepository($container->getByType(Explorer::class)));
        $container->addService('categoryRepository', new CategoryRepository($container->getByType(Explorer::class)));
        $container->addService('reviewRepository', new ReviewRepository(
            $container->getByType(Explorer::class), 
            $container->getService('addonRepository')
        ));
        $container->addService('tagRepository', new TagRepository($container->getByType(Explorer::class)));
        
        // Register services
        $container->addService('addonService', new AddonService(
            $container->getService('addonRepository'),
            $container->getParameters()['uploadsDir'] ?? 'uploads'
        ));
        $container->addService('authorService', new AuthorService($container->getService('authorRepository')));
        $container->addService('categoryService', new CategoryService($container->getService('categoryRepository')));
        $container->addService('reviewService', new ReviewService($container->getService('reviewRepository')));
        $container->addService('tagService', new TagService($container->getService('tagRepository')));
        $container->addService('statisticsService', new StatisticsService(
            $container->getService('addonRepository'),
            $container->getService('authorRepository'),
            $container->getService('categoryRepository'),
            $container->getService('reviewRepository'),
            $container->getByType(Explorer::class)
        ));
        $container->addService('searchService', new SearchService(
            $container->getService('addonRepository'),
            $container->getService('authorRepository'),
            $container->getService('tagRepository')
        ));
    }
}