<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\SavedFiltersOwnerAccessor;

use Presta\SonataSavedFiltersBundle\Entity\SavedFiltersOwnerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AuthenticatedUserSavedFiltersOwnerAccessor implements SavedFiltersOwnerAccessorInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly KernelInterface $kernel,
    ) {
    }

    public function get(): SavedFiltersOwnerInterface
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (null === $user) {
            // In CLI, there is no authenticated user.
            // Only return a dummy object if not in test environment.
            if (PHP_SAPI === 'cli' && 'test' !== $this->kernel->getEnvironment()) {
                // Return a dummy object that satisfies the interface to prevent commands from crashing.
                return new class() implements SavedFiltersOwnerInterface {
                };
            }

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
