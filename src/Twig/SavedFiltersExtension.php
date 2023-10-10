<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Twig;

use Presta\SonataSavedFiltersBundle\Repository\SavedFiltersRepository;
use Presta\SonataSavedFiltersBundle\SavedFiltersOwnerAccessor\CannotAccessSavedFiltersOwnerException;
use Presta\SonataSavedFiltersBundle\SavedFiltersOwnerAccessor\SavedFiltersOwnerAccessorInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SavedFiltersExtension extends AbstractExtension
{
    public function __construct(
        private readonly SavedFiltersRepository $repository,
        private readonly SavedFiltersOwnerAccessorInterface $filterSetHolderAccessor,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'get_filters_sets',
                function (AbstractAdmin $admin) {
                    try {
                        $owner = $this->filterSetHolderAccessor->get();
                    } catch (CannotAccessSavedFiltersOwnerException) {
                        return [];
                    }

                    return $this->repository->findAccessibleForAdmin($admin->getClass(), $owner);
                },
            ),
        ];
    }
}
