<?php

namespace App\Test\Entity\Sub1\SubSub1;

use App\Test\Entity\Root;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SubSubEntity2
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'subSubEntity2')]
    private ?Root $root = null;

    #[ORM\OneToOne(mappedBy: 'subSubEntity2', cascade: ['persist', 'remove'])]
    private ?SubSubEntity1 $subSubEntity1 = null;
}
