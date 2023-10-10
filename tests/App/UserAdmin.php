<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Tests\App;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

final class UserAdmin extends AbstractAdmin
{
    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'user';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'user';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('username');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('username');
    }
}
