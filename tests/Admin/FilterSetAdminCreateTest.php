<?php

declare(strict_types=1);

namespace Admin;

use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Tests\Admin\AdminTestCase;
use Presta\SonataSavedFiltersBundle\Tests\App\User;
use Symfony\Component\HttpFoundation\Response;

final class FilterSetAdminCreateTest extends AdminTestCase
{
    public function testCreate(): void
    {
        // Given
        self::$doctrine->persist($admin = new User('admin'));
        self::$doctrine->flush();
        self::$client->loginUser($admin);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count([]));

        // When
        self::$client->request('POST', '/presta/sonata-filters-set/filters-set/create', [
            'name' => 'My precious filter',
            'adminClass' => User::class,
            'filters' => 'filter%5Busername%5D%5Bvalue%5D=john',
        ]);

        // Then
        self::assertResponseIsSuccessful();
        self::assertJson(self::$client->getResponse()->getContent());
        self::assertSame(1, self::$doctrine->getRepository(SavedFilters::class)->count([]));
    }

    public function testCreateInvalid(): void
    {
        // Given
        self::$doctrine->persist($admin = new User('admin'));
        self::$doctrine->flush();
        self::$client->loginUser($admin);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count([]));

        // When
        self::$client->request('POST', '/presta/sonata-filters-set/filters-set/create', [
            'name' => null,
            'adminClass' => null,
            'filters' => null,
        ]);

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJson(self::$client->getResponse()->getContent());
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count([]));
    }

    public function testCreateNotAuthenticated(): void
    {
        // Given
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count([]));

        // When
        self::$client->request('POST', '/presta/sonata-filters-set/filters-set/create');

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count([]));
    }
}
