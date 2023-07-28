<?php

namespace App\Test\Gedmo;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[Gedmo\Tree(type: 'nested')]
class Tree
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft')]
    private ?int $lft = null;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl')]
    private ?int $lvl = null;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt')]
    private ?int $rgt = null;

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: Tree::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Tree $root = null;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: Tree::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Tree $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Tree::class)]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private ?Collection $children;
}
