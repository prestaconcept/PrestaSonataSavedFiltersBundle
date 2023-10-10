<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Presta\SonataSavedFiltersBundle\Entity\SavedFilters;
use Presta\SonataSavedFiltersBundle\Entity\SavedFiltersOwnerInterface;

/**
 * @extends ServiceEntityRepository<SavedFilters>
 */
final class SavedFiltersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedFilters::class);
    }

    /**
     * @param class-string $adminClass
     *
     * @return array<SavedFilters>
     */
    public function findAccessibleForAdmin(string $adminClass, SavedFiltersOwnerInterface $owner): array
    {
        $queryBuilder = $this->createQueryBuilder('saved_filters');
        $queryBuilder
            ->innerJoin('saved_filters.ownersWithAccess', 'owners')
            ->where('saved_filters.adminClass = :admin_class')
            ->andWhere('owners = :owner')
            ->setParameter('admin_class', $adminClass)
            ->setParameter('owner', $owner)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
