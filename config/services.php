<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Presta\SonataSavedFiltersBundle\Admin\FiltersSetAdmin;
use Presta\SonataSavedFiltersBundle\Controller\SavedFiltersController;
use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Event\RemoveOrphanedSavedFiltersListener;
use Presta\SonataSavedFiltersBundle\Repository\SavedFiltersRepository;
use Presta\SonataSavedFiltersBundle\SavedFiltersOwnerAccessor\AuthenticatedUserSavedFiltersOwnerAccessor;
use Presta\SonataSavedFiltersBundle\SavedFiltersOwnerAccessor\SavedFiltersOwnerAccessorInterface;
use Presta\SonataSavedFiltersBundle\Twig\SavedFiltersExtension;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set(FiltersSetAdmin::class)
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'label' => 'filters_set.name',
            'model_class' => SavedFilters::class,
            'controller' => SavedFiltersController::class,
        ])
        ->args([
            service(SavedFiltersOwnerAccessorInterface::class),
        ])
        ->call('setTranslationDomain', ['PrestaSonataSavedFiltersBundle'])

        ->set(SavedFiltersController::class)
        ->autowire()
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber')
        ->args([
            service('doctrine.orm.entity_manager'),
            service(SavedFiltersOwnerAccessorInterface::class),
            service('validator'),
        ])

        ->alias(SavedFiltersOwnerAccessorInterface::class, AuthenticatedUserSavedFiltersOwnerAccessor::class)

        ->set(AuthenticatedUserSavedFiltersOwnerAccessor::class)
        ->args([
            service(TokenStorageInterface::class),
        ])

        ->set(RemoveOrphanedSavedFiltersListener::class)
        ->tag('doctrine.event_listener', ['event' => Events::postUpdate])
        ->tag('doctrine.event_listener', ['event' => Events::postFlush])
        ->args([
            service('request_stack'),
            service('translator'),
        ])

        ->set(SavedFiltersRepository::class)
        ->tag('doctrine.repository_service')
        ->args([
            service('doctrine'),
        ])

        ->set(SavedFiltersExtension::class)
        ->tag('twig.extension')
        ->args([
            service(SavedFiltersRepository::class),
            service(SavedFiltersOwnerAccessorInterface::class),
        ])
    ;
};
