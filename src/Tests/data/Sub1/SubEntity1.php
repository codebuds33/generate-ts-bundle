<?php

namespace App\Test\Sub1;

use App\Test\Root;
use App\Test\Sub1\SubSub1\SubSubEntity1;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SubEntity1
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Root::class, mappedBy: 'subEntity1')]
    private Collection $roots;

    #[ORM\OneToOne(mappedBy: 'subEntity1', cascade: ['persist', 'remove'])]
    private ?SubSubEntity1 $subSubEntity1 = null;
}
