<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Entity\SavedFiltersOwnerInterface;
use Presta\SonataSavedFiltersBundle\SavedFiltersOwnerAccessor\CannotAccessSavedFiltersOwnerException;
use Presta\SonataSavedFiltersBundle\SavedFiltersOwnerAccessor\SavedFiltersOwnerAccessorInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @extends CRUDController<SavedFilters>
 */
final class SavedFiltersController extends CRUDController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SavedFiltersOwnerAccessorInterface $filterSetHolderAccessor,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function createAction(Request $request): JsonResponse
    {
        $filtersSet = new SavedFilters();
        $filtersSet->setName($request->request->get('name'));
        $filtersSet->setAdminClass($request->request->get('adminClass'));
        $filtersSet->setFilters($request->request->get('filters'));
        $filtersSet->grantOwner($this->owner());

        $violations = $this->validator->validate($filtersSet);
        if (count($violations) > 0) {
            return $this->json($violations, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($filtersSet);
        $this->entityManager->flush();

        return $this->json(
            [
                'name' => $filtersSet->getName(),
                'filters' => $filtersSet->getFiltersQueryString(),
            ],
            Response::HTTP_CREATED,
        );
    }

    public function protectAction(int $id): RedirectResponse
    {
        $this->owner(); // will guarantee an exception is thrown if no user is available

        if (!$this->admin->isGranted('PROTECT')) {
            throw $this->createAccessDeniedException();
        }

        $object = $this->filter($id);
        $object->setProtected(true);

        $this->entityManager->flush();

        $this->addFlash('success', $this->trans('saved_filters.flash.protected'));

        return $this->redirectToList();
    }

    public function unprotectAction(int $id): RedirectResponse
    {
        $this->owner(); // will guarantee an exception is thrown if no user is available

        if (!$this->admin->isGranted('PROTECT')) {
            throw $this->createAccessDeniedException();
        }

        $object = $this->filter($id);
        $object->setProtected(false);

        $this->entityManager->flush();

        $this->addFlash('success', $this->trans('saved_filters.flash.unprotected'));

        return $this->redirectToList();
    }

    public function shareAction(int $id): RedirectResponse
    {
        $this->owner(); // will guarantee an exception is thrown if no user is available

        $object = $this->filter($id);
        $object->setPublic(true);

        $this->entityManager->flush();

        $this->addFlash('success', $this->trans('saved_filters.flash.shared'));

        return $this->redirectToList();
    }

    public function subscribeAction(int $id): RedirectResponse
    {
        $object = $this->filter($id);
        $object->grantOwner($this->owner());

        $this->entityManager->flush();

        $this->addFlash('success', $this->trans('saved_filters.flash.subscribed'));

        return $this->redirectToList();
    }

    public function unsubscribeAction(int $id): RedirectResponse
    {
        $object = $this->filter($id);
        $object->revokeOwner($this->owner());

        $this->entityManager->flush();

        $this->addFlash('success', $this->trans('saved_filters.flash.unsubscribed'));

        return $this->redirectToList();
    }

    private function filter(int $id): SavedFilters
    {
        $object = $this->admin->getObject($id);
        if ($object === null) {
            throw $this->createNotFoundException(sprintf(
                'Unable to find %s object with id: %s.',
                $this->admin->getClassnameLabel(),
                $id
            ));
        }

        return $object;
    }

    private function owner(): SavedFiltersOwnerInterface
    {
        try {
            return $this->filterSetHolderAccessor->get();
        } catch (CannotAccessSavedFiltersOwnerException $exception) {
            throw $this->createAccessDeniedException(previous: $exception);
        }
    }
}
