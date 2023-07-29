<?php

namespace App\Test\Entity\Sub1\SubSub1;

use App\Test\Entity\Sub1\SubEntity1;
use App\Test\Entity\Sub2\SubEntity2;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SubSubEntity1
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'subSubEntity1', cascade: ['persist', 'remove'])]
    private ?SubEntity2 $subEntity2 = null;

    #[ORM\OneToOne(inversedBy: 'subSubEntity1', cascade: ['persist', 'remove'])]
    private ?SubSubEntity2 $subSubEntity2 = null;

    #[ORM\OneToOne(inversedBy: 'subSubEntity1', cascade: ['persist', 'remove'])]
    private ?SubEntity1 $subEntity1 = null;
}
