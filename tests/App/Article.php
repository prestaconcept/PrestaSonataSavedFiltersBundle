<?php

declare(strict_types=1);

namespace App;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Article
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $name;
}
