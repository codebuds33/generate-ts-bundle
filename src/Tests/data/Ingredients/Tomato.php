<?php

namespace App\Test\Ingredients;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Ingredients\TomatoRepository;
use App\Test\IngredientCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TomatoRepository::class)]
#[ApiResource]
class Tomato
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: IngredientCategory::class)]
    private Collection $category;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, IngredientCategory>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(IngredientCategory $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(IngredientCategory $category): static
    {
        $this->category->removeElement($category);

        return $this;
    }
}
