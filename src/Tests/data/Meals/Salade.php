<?php

namespace App\Test\Meals;

use App\Test\Ingredients\Cucumber;
use App\Test\Ingredients\Tomato;
use App\Repository\Meals\SaladeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaladeRepository::class)]
class Salade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Tomato::class)]
    private Collection $tomato;

    #[ORM\ManyToOne]
    private ?Cucumber $cucumber = null;

    public function __construct()
    {
        $this->tomato = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Tomato>
     */
    public function getTomato(): Collection
    {
        return $this->tomato;
    }

    public function addTomato(Tomato $tomato): static
    {
        if (!$this->tomato->contains($tomato)) {
            $this->tomato->add($tomato);
        }

        return $this;
    }

    public function removeTomato(Tomato $tomato): static
    {
        $this->tomato->removeElement($tomato);

        return $this;
    }

    public function getCucumber(): ?Cucumber
    {
        return $this->cucumber;
    }

    public function setCucumber(?Cucumber $cucumber): static
    {
        $this->cucumber = $cucumber;

        return $this;
    }
}
