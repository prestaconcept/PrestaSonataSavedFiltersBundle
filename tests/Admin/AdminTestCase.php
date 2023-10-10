<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Tests\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Presta\SonataSavedFiltersBundle\Tests\DatabaseTestHelper;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

abstract class AdminTestCase extends WebTestCase
{
    protected static ?KernelBrowser $client = null;
    protected static ?EntityManagerInterface $doctrine = null;

    protected function setUp(): void
    {
        parent::setUp();

        self::$client = self::createClient();
        self::$doctrine = self::getContainer()->get('doctrine.orm.default_entity_manager');
        DatabaseTestHelper::rebuild(self::getContainer());
    }

    protected function assertPageContainsCountElement(int $count, Crawler $page): void
    {
        self::assertCount($count, $page->filter('.sonata-ba-list > tbody > tr'));
    }
}
