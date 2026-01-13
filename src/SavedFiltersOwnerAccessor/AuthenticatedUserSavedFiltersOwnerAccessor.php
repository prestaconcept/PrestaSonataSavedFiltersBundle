<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\SavedFiltersOwnerAccessor;

use Presta\SonataSavedFiltersBundle\Entity\SavedFiltersOwnerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AuthenticatedUserSavedFiltersOwnerAccessor implements SavedFiltersOwnerAccessorInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function get(): SavedFiltersOwnerInterface
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (null === $user) {
            throw new CannotAccessSavedFiltersOwnerException('Missing authenticated user.');
        }

        if (!$user instanceof SavedFiltersOwnerInterface) {
            throw new CannotAccessSavedFiltersOwnerException(
                \sprintf('Authenticated user %s does not implements %s', $user::class, SavedFiltersOwnerInterface::class),
            );
        }

        return $user;
    }
}
