<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Tests\Admin;

use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Tests\App\User;

final class SavedFiltersAdminListTest extends AdminTestCase
{
    public function testList(): void
    {
        // Given
        self::$doctrine->persist($user = new User('admin'));
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('My filters');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=john');
        $filter->setAdminClass(User::class);
        $filter->grantOwner($user);
        self::$doctrine->flush();
        self::$client->loginUser($user);

        // When
        $page = self::$client->request('GET', '/presta/sonata-saved-filters/saved-filters/list');

        // Then
        self::assertResponseIsSuccessful();
        self::assertPageContainsCountElement(1, $page);
    }
}
