<?php

declare(strict_types=1);

namespace Admin;

use App\Article;
use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Tests\Admin\AdminTestCase;
use Presta\SonataSavedFiltersBundle\Tests\App\User;

final class UserAdminListTest extends AdminTestCase
{
    public function testListNoFilters(): void
    {
        // Given
        self::$doctrine->persist($user = new User('admin'));
        self::$doctrine->flush();
        self::$client->loginUser($user);

        // When
        self::$client->request('GET', '/user/list');

        // Then
        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.filters-sets > .dropdown > .dropdown-toggle', 'Saved filters 0');
        self::assertSelectorTextSame('.filters-sets .filters-sets-list .create-filters-set', 'Save current state');
    }

    public function testListFilters(): void
    {
        // Given
        self::$doctrine->persist($user = new User('admin'));
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('Users named john');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=john');
        $filter->setAdminClass(User::class);
        $filter->grantOwner($user);
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('Users named jane');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=jane');
        $filter->setAdminClass(User::class);
        $filter->grantOwner($user);
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('Private filter');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=admin');
        $filter->setAdminClass(User::class);
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('Article filter');
        $filter->setFilters('filter%5Bname%5D%5Bvalue%5D=lego');
        $filter->setAdminClass(Article::class);
        self::$doctrine->flush();
        self::$client->loginUser($user);

        // When
        self::$client->request('GET', '/user/list');

        // Then
        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.filters-sets > .dropdown > .dropdown-toggle', 'Saved filters 2');
        self::assertSelectorTextContains('.filters-sets .filters-sets-list', 'Users named john');
        self::assertSelectorTextContains('.filters-sets .filters-sets-list', 'Users named jane');
        self::assertSelectorTextNotContains('.filters-sets .filters-sets-list', 'Private filter');
    }

    public function testListFiltered(): void
    {
        // Given
        self::$doctrine->persist(new User('john.jackson'));
        self::$doctrine->persist(new User('john.doe'));
        self::$doctrine->persist(new User('jack.doe'));
        self::$doctrine->persist($user = new User('admin'));
        self::$doctrine->persist($filter = new SavedFilters());
        $filter->setName('Users named john');
        $filter->setFilters('filter%5Busername%5D%5Bvalue%5D=john');
        $filter->setAdminClass(User::class);
        $filter->grantOwner($user);
        self::$doctrine->flush();
        self::$client->loginUser($user);

        // When
        self::$client->request('GET', '/user/list');
        $page = self::$client->clickLink('Users named john');

        // Then
        self::assertResponseIsSuccessful();
        self::assertPageContainsCountElement(2, $page);
    }
}
