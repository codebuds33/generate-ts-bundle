<?php

namespace App\Test\Sub2;

use App\Test\Sub1\SubSub1\SubSubEntity1;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SubEntity2
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'subEntity2', cascade: ['persist', 'remove'])]
    private ?SubSubEntity1 $subSubEntity1 = null;
}
