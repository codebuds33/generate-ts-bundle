<?php

namespace App\Test\Ingredients;

use App\Test\IngredientCategory;
use App\Repository\Ingredients\CucumberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CucumberRepository::class)]
class Cucumber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: IngredientCategory::class, inversedBy: 'cucumbers')]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, IngredientCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(IngredientCategory $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(IngredientCategory $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }
}
