<?php

declare(strict_types=1);

use Symfony\Component\Filesystem\Filesystem;

require_once __DIR__ . '/../vendor/autoload.php';

$filesystem = new Filesystem();
$filesystem->remove(__DIR__ . '/../var');
$filesystem->mkdir(__DIR__ . '/../var/log');
