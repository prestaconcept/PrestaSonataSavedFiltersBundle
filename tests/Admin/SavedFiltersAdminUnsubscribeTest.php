<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Tests\Admin;

use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Tests\App\User;
use Symfony\Component\HttpFoundation\Response;

final class SavedFiltersAdminUnsubscribeTest extends AdminTestCase
{
    public function testUnsubscribe(): void
    {
        // Given
        self::$doctrine->persist($user = new User('admin'));
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('My precious filter');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=john');
        $filter->setAdminClass(User::class);
        $filter->grantOwner($user);
        self::$doctrine->flush();
        self::$client->loginUser($user);
        self::assertCount(
            1,
            self::$doctrine->getRepository(SavedFilters::class)->findAccessibleForAdmin(User::class, $user),
        );

        // When
        self::$client->request('POST', "/presta/sonata-saved-filters/saved-filters/{$filter->getId()}/unsubscribe");

        // Then
        self::assertResponseRedirects();
        self::$client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-success', 'The saved filter was removed from your favorites.');
        self::assertCount(
            0,
            self::$doctrine->getRepository(SavedFilters::class)->findAccessibleForAdmin(User::class, $user),
        );
    }

    public function testUnsubscribeNotFound(): void
    {
        // Given
        self::$doctrine->persist($user = new User('admin'));
        self::$doctrine->flush();
        self::$client->loginUser($user);
        self::assertCount(
            0,
            self::$doctrine->getRepository(SavedFilters::class)->findAccessibleForAdmin(User::class, $user),
        );

        // When
        self::$client->request('POST', '/presta/sonata-saved-filters/saved-filters/1/unsubscribe');

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertCount(
            0,
            self::$doctrine->getRepository(SavedFilters::class)->findAccessibleForAdmin(User::class, $user),
        );
    }

    public function testUnsubscribeNotAuthenticated(): void
    {
        // Given
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('My precious filter');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=john');
        $filter->setAdminClass(User::class);
        self::$doctrine->flush();

        // When
        self::$client->request('POST', "/presta/sonata-saved-filters/saved-filters/{$filter->getId()}/unsubscribe");

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
