<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\SavedFiltersOwnerAccessor;

use Presta\SonataSavedFiltersBundle\Entity\SavedFiltersOwnerInterface;

interface SavedFiltersOwnerAccessorInterface
{
    public function get(): SavedFiltersOwnerInterface;
}
