<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Tests\Admin;

use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Tests\App\User;
use Symfony\Component\HttpFoundation\Response;

final class SavedFiltersAdminNewTest extends AdminTestCase
{
    public function testNew(): void
    {
        // Given
        self::$doctrine->persist($admin = new User('admin'));
        self::$doctrine->flush();
        self::$client->loginUser($admin);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count([]));

        // When
        self::$client->request('POST', '/presta/sonata-saved-filters/saved-filters/new', [
            'name' => 'My precious filter',
            'adminClass' => User::class,
            'filters' => 'filter%5Busername%5D%5Bvalue%5D=john',
        ]);

        // Then
        self::assertResponseIsSuccessful();
        self::assertJson(self::$client->getResponse()->getContent());
        self::assertSame(1, self::$doctrine->getRepository(SavedFilters::class)->count([]));
    }

    public function testNewInvalid(): void
    {
        // Given
        self::$doctrine->persist($admin = new User('admin'));
        self::$doctrine->flush();
        self::$client->loginUser($admin);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count([]));

        // When
        self::$client->request('POST', '/presta/sonata-saved-filters/saved-filters/new', [
            'name' => null,
            'adminClass' => null,
            'filters' => null,
        ]);

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJson(self::$client->getResponse()->getContent());
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count([]));
    }

    public function testNewNotAuthenticated(): void
    {
        // Given
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count([]));

        // When
        self::$client->request('POST', '/presta/sonata-saved-filters/saved-filters/new');

        // Then
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertSame(0, self::$doctrine->getRepository(SavedFilters::class)->count([]));
    }
}
