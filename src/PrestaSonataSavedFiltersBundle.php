<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class PrestaSonataSavedFiltersBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
