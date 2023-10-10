<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Presta\SonataSavedFiltersBundle\Repository\SavedFiltersRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SavedFiltersRepository::class)]
#[ORM\Table(name: 'presta_sonata_saved_filters')]
class SavedFilters
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?string $name = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?string $adminClass = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: 'json')]
    #[Assert\NotNull]
    private ?array $filters = null;

    #[ORM\Column(type: 'boolean')]
    private bool $public = false;

    #[ORM\Column(type: 'boolean')]
    private bool $protected = false;

    /**
     * @var Collection<int, SavedFiltersOwnerInterface>
     */
    #[ORM\ManyToMany(targetEntity: SavedFiltersOwnerInterface::class)]
    #[ORM\JoinTable(name: 'presta_sonata_saved_filters_owners')]
    #[ORM\JoinColumn(name: 'filters_id')]
    #[ORM\InverseJoinColumn(name: 'owner_id')]
    private Collection $ownersWithAccess;

    public function __construct()
    {
        $this->ownersWithAccess = new ArrayCollection();
    }

    public function getFiltersQueryString(): string
    {
        if (!is_array($this->filters) || 0 === count($this->filters)) {
            return '';
        }

        return '?' . http_build_query($this->filters);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getAdminClass(): ?string
    {
        return $this->adminClass;
    }

    public function setAdminClass(?string $adminClass): void
    {
        $this->adminClass = $adminClass;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function setFilters(?string $filters): void
    {
        if (null === $filters) {
            return;
        }

        parse_str($filters, $this->filters);
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function isProtected(): bool
    {
        return $this->protected;
    }

    public function setProtected(bool $protected): void
    {
        $this->protected = $protected;
    }

    /**
     * @return array<SavedFiltersOwnerInterface>
     */
    public function getOwnersWithAccess(): array
    {
        return $this->ownersWithAccess->toArray();
    }

    public function grantOwner(SavedFiltersOwnerInterface $owner): void
    {
        if (!$this->ownersWithAccess->contains($owner)) {
            $this->ownersWithAccess->add($owner);
        }
    }

    public function revokeOwner(SavedFiltersOwnerInterface $owner): void
    {
        if ($this->ownersWithAccess->contains($owner)) {
            $this->ownersWithAccess->removeElement($owner);
        }
    }
}
