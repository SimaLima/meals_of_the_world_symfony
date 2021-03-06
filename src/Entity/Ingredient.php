<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=IngredientRepository::class)
 */
class Ingredient
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity=Meal::class, mappedBy="ingredient")
     */
    private $meal;

    /**
     * @Gedmo\Locale
     */
    private $locale;


    public function __construct()
    {
        $this->meal = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|Meal[]
     */
    public function getMeal(): Collection
    {
        return $this->meal;
    }

    public function addMeal(Meal $meal): self
    {
        if (!$this->meal->contains($meal)) {
            $this->meal[] = $meal;
            $meal->addIngredient($this);
        }

        return $this;
    }

    public function removeMeal(Meal $meal): self
    {
        if ($this->meal->contains($meal)) {
            $this->meal->removeElement($meal);
            $meal->removeIngredient($this);
        }

        return $this;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
}
