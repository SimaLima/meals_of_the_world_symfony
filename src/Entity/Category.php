<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
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
     * @ORM\OneToMany(targetEntity=Meal::class, mappedBy="category")
     */
    private $meal;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

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
            $meal->setCategory($this);
        }

        return $this;
    }

    public function removeMeal(Meal $meal): self
    {
        if ($this->meal->removeElement($meal)) {
            // set the owning side to null (unless already changed)
            if ($meal->getCategory() === $this) {
                $meal->setCategory(null);
            }
        }

        return $this;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
