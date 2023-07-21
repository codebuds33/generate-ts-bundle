<?php

namespace App\Test;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\IngredientCategoryRepository;
use App\State\IngredientCategory\CategoryChildrenProvider;
use App\State\IngredientCategory\RootCategoriesProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/ingredient_categories/roots',
            name: 'get_root_ingredient_categories',
            provider: RootCategoriesProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/ingredient_categories/{id}/children',
            requirements: ['id' => '\d+'],
            name: 'get_children',
              provider: CategoryChildrenProvider::class,
        ),
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['ingredient_category:read']],
    denormalizationContext: ['groups' => ['ingredient_category:write']],
)]
#[Gedmo\Tree(type: 'nested')]
#[ORM\Entity(repositoryClass: IngredientCategoryRepository::class)]
class IngredientCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ingredient_category:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['ingredient_category:read'])]
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

    #[Groups(['ingredient_category:read'])]
    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: 'IngredientCategory')]
    #[ORM\JoinColumn(nullable: true)]
    private ?IngredientCategory $root = null;

    #[Groups(['ingredient_category:read'])]
    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: 'IngredientCategory', inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true)]
    private ?IngredientCategory $parent = null;

    #[Groups(['ingredient_category:read'])]
    #[MaxDepth(1)]
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: 'IngredientCategory')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private ?Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): IngredientCategory
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): IngredientCategory
    {
        $this->name = $name;
        return $this;
    }

    public function getRoot(): ?IngredientCategory
    {
        return $this->root;
    }

    public function getParent(): ?IngredientCategory
    {
        return $this->parent;
    }

    public function setParent(?IngredientCategory $parent): IngredientCategory
    {
        $this->parent = $parent;
        return $this;
    }

    public function getChildren(): ?Collection
    {
        return $this->children;
    }

    public function setChildren(?Collection $children): IngredientCategory
    {
        $this->children = $children;
        return $this;
    }
}
