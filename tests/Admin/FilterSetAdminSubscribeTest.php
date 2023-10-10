<?php

declare(strict_types=1);

namespace Admin;

use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Tests\Admin\AdminTestCase;
use Presta\SonataSavedFiltersBundle\Tests\App\User;
use Symfony\Component\HttpFoundation\Response;

final class FilterSetAdminSubscribeTest extends AdminTestCase
{
    public function testSubscribe(): void
    {
        // Given
        self::$doctrine->persist($user = new User('admin'));
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('My precious filter');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=john');
        $filter->setAdminClass(User::class);
        self::$doctrine->flush();
        self::$client->loginUser($user);
        self::assertCount(
            0,
            self::$doctrine->getRepository(SavedFilters::class)->findAccessibleForAdmin(User::class, $user),
        );

        // When
        self::$client->request('PUT', "/presta/sonata-filters-set/filters-set/{$filter->getId()}/subscribe");

        // Then
        self::assertResponseRedirects();
        self::$client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-success', 'The saved filter was added to your favorites.');
        self::assertCount(
            1,
            self::$doctrine->getRepository(SavedFilters::class)->findAccessibleForAdmin(User::class, $user),
        );
    }

    public function testSubscribeNotFound(): void
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
        self::$client->request('PUT', '/presta/sonata-filters-set/filters-set/1/subscribe');

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertCount(
            0,
            self::$doctrine->getRepository(SavedFilters::class)->findAccessibleForAdmin(User::class, $user),
        );
    }

    public function testSubscribeNotAuthenticated(): void
    {
        // Given
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('My precious filter');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=john');
        $filter->setAdminClass(User::class);
        self::$doctrine->flush();

        // When
        self::$client->request('PUT', "/presta/sonata-filters-set/filters-set/{$filter->getId()}/subscribe");

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
