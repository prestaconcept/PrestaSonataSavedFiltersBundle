<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Event;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RemoveOrphanedSavedFiltersListener
{
    /**
     * @var array<SavedFilters>
     */
    private array $orphanFiltersSets = [];

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function postUpdate(PostUpdateEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof SavedFilters) {
            return;
        }

        if (count($entity->getOwnersWithAccess()) > 0 || $entity->isProtected()) {
            return;
        }

        $this->orphanFiltersSets[] = $entity;
    }

    public function postFlush(PostFlushEventArgs $event): void
    {
        if (0 === count($this->orphanFiltersSets)) {
            return;
        }

        $entityManager = $event->getObjectManager();

        foreach ($this->orphanFiltersSets as $filtersSet) {
            $entityManager->remove($filtersSet);
        }

        $this->orphanFiltersSets = [];

        $session = $this->requestStack->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->add(
                'warning',
                $this->translator->trans('saved_filters.flash.deleted', [], 'PrestaSonataSavedFiltersBundle'),
            );
        }

        $entityManager->flush();
    }
}
