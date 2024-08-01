<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use Psr\Container\ContainerInterface;

class DatabaseTestHelper
{
    public static function rebuild(ContainerInterface $container): void
    {
        $doctrine = $container->get('doctrine.orm.default_entity_manager');
        $connection = $doctrine->getConnection();

        $database = $connection->getParams()['path'];
        if (file_exists($database)) {
            unlink($database);
            touch($database);
        } else {
            $schema = $connection->createSchemaManager();
            $schema->createDatabase($database);
        }

        (new SchemaTool($doctrine))
            ->createSchema($doctrine->getMetadataFactory()->getAllMetadata());
    }
}
