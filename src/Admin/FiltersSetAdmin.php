<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Admin;

use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Exception\UnexpectedTypeException;
use Presta\SonataSavedFiltersBundle\SavedFiltersOwnerAccessor\SavedFiltersOwnerAccessorInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractAdmin<SavedFilters>
 */
final class FiltersSetAdmin extends AbstractAdmin
{
    public function __construct(
        private readonly SavedFiltersOwnerAccessorInterface $filterSetHolderAccessor,
    ) {
        parent::__construct();
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'presta_sonatafiltersset_filtersset';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return '/presta/sonata-filters-set/filters-set';
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);
        if (!$query instanceof ProxyQuery) {
            throw new UnexpectedTypeException($query, ProxyQuery::class);
        }

        $rootAlias = current($query->getRootAliases());

        $query
            ->leftJoin("{$rootAlias}.ownersWithAccess", 'owners')
            ->andWhere(
                $query->expr()->orX(
                    "{$rootAlias}.public = TRUE",
                    "owners = :owner",
                ),
            )
            ->setParameter('owner', $this->filterSetHolderAccessor->get())
        ;

        return $query;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'create']);

        $collection->add(
            name: 'protect',
            pattern: "{$this->getRouterIdParameter()}/protect",
            methods: [Request::METHOD_PUT],
        );
        $collection->add(
            name: 'unprotect',
            pattern: "{$this->getRouterIdParameter()}/unprotect",
            methods: [Request::METHOD_PUT],
        );
        $collection->add(
            name: 'share',
            pattern: "{$this->getRouterIdParameter()}/share",
            methods: [Request::METHOD_PUT],
        );
        $collection->add(
            name: 'subscribe',
            pattern: "{$this->getRouterIdParameter()}/subscribe",
            methods: [Request::METHOD_PUT],
        );
        $collection->add(
            name: 'unsubscribe',
            pattern: "{$this->getRouterIdParameter()}/unsubscribe",
            methods: [Request::METHOD_PUT],
        );
    }

    protected function configureDashboardActions(array $actions): array
    {
        $actions = parent::configureDashboardActions($actions);
        unset($actions['create']);

        return $actions;
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $actions = parent::configureActionButtons($buttonList, $action, $object);
        unset($actions['create']);

        return $actions;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('name')
            ->add('adminClass')
            ->add('filters', null, ['inline' => false])
            ->add('public')
        ;

        if ($this->isGranted('PROTECT')) {
            $list->add('protected');
        }

        $list
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'share' => [
                            'template' => '@PrestaSonataSavedFilters/admin/filters_set/list_action_share.html.twig',
                        ],
                        'protect' => [
                            'template' => '@PrestaSonataSavedFilters/admin/filters_set/list_action_protect.html.twig',
                        ],
                        'unprotect' => [
                            'template' => '@PrestaSonataSavedFilters/admin/filters_set/list_action_unprotect.html.twig',
                        ],
                        'subscribe' => [
                            'template' => '@PrestaSonataSavedFilters/admin/filters_set/list_action_subscribe.html.twig',
                        ],
                        'unsubscribe' => [
                            'template' => '@PrestaSonataSavedFilters/admin/filters_set/list_action_unsubscribe.html.twig',
                        ],
                    ],
                ],
            )
        ;
    }
}
