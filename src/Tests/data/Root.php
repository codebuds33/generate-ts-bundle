<?php

namespace App\Test;

use App\Repository\RootRepository;
use App\Test\Sub1\SubEntity1;
use App\Test\Sub1\SubSub1\SubSubEntity1;
use App\Test\Sub1\SubSub1\SubSubEntity2;
use App\Test\Sub2\SubEntity2;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RootRepository::class)]
class Root
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?SubSubEntity1 $subSubEntity1 = null;

    #[ORM\OneToMany(mappedBy: 'root', targetEntity: SubSubEntity2::class)]
    private Collection $subSubEntity2;

    #[ORM\ManyToMany(targetEntity: SubEntity1::class, inversedBy: 'roots')]
    private Collection $subEntity1;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?SubEntity2 $subEntity2 = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $datetime = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;
}
