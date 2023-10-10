<?php

declare(strict_types=1);

namespace Admin;

use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Tests\Admin\AdminTestCase;
use Presta\SonataSavedFiltersBundle\Tests\App\User;
use Symfony\Component\HttpFoundation\Response;

final class FilterSetAdminProtectTest extends AdminTestCase
{
    public function testProtect(): void
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
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => true]));

        // When
        self::$client->request('PUT', "/presta/sonata-filters-set/filters-set/{$filter->getId()}/protect");

        // Then
        self::assertResponseRedirects();
        self::$client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-success', 'The saved filter is now protected.');
        self::assertSame(1, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => true]));
    }

    public function testProtectNotFound(): void
    {
        // Given
        self::$doctrine->persist($user = new User('admin'));
        self::$doctrine->flush();
        self::$client->loginUser($user);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => true]));

        // When
        self::$client->request('PUT', '/presta/sonata-filters-set/filters-set/1/protect');

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => true]));
    }

    public function testProtectNotAuthenticated(): void
    {
        // Given
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('My precious filter');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=john');
        $filter->setAdminClass(User::class);
        self::$doctrine->flush();
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => true]));

        // When
        self::$client->request('PUT', "/presta/sonata-filters-set/filters-set/{$filter->getId()}/protect");

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count(['protected' => true]));
    }
}
