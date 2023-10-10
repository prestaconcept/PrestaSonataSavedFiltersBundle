<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class PrestaSonataSavedFiltersExtension extends Extension
{
    /**
     * @param array<array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__) . '/../config'));
        $loader->load('services.php');
    }
}
