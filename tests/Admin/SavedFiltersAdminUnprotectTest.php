<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Tests\Admin;

use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Tests\App\User;
use Symfony\Component\HttpFoundation\Response;

final class SavedFiltersAdminUnprotectTest extends AdminTestCase
{
    public function testUnprotect(): void
    {
        // Given
        self::$doctrine->persist($user = new User('admin'));
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('My precious filter');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=john');
        $filter->setAdminClass(User::class);
        $filter->grantOwner($user);
        $filter->setProtected(true);
        self::$doctrine->flush();
        self::$client->loginUser($user);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => false]));

        // When
        self::$client->request('PUT', "/presta/sonata-saved-filters/saved-filters/{$filter->getId()}/unprotect");

        // Then
        self::assertResponseRedirects();
        self::$client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-success', 'The saved filter is now unprotected.');
        self::assertSame(1, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => false]));
    }

    public function testUnprotectNotFound(): void
    {
        // Given
        self::$doctrine->persist($user = new User('admin'));
        self::$doctrine->flush();
        self::$client->loginUser($user);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => false]));

        // When
        self::$client->request('PUT', '/presta/sonata-saved-filters/saved-filters/1/unprotect');

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => false]));
    }

    public function testUnprotectNotAuthenticated(): void
    {
        // Given
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('My precious filter');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=john');
        $filter->setAdminClass(User::class);
        $filter->setProtected(true);
        self::$doctrine->flush();
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => false]));

        // When
        self::$client->request('PUT', "/presta/sonata-saved-filters/saved-filters/{$filter->getId()}/unprotect");

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => false]));
    }
}
