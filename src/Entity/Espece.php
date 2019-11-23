<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EspeceRepository")
 */
class Espece
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $info_sup;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $pouvoirs;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $faiblesses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Personnage", mappedBy="espece")
     */
    private $personnages;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag", inversedBy="especes")
     */
    private $tag;

    public function __construct()
    {
        $this->personnages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
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

    public function getInfoSup(): ?string
    {
        return $this->info_sup;
    }

    public function setInfoSup(?string $info_sup): self
    {
        $this->info_sup = $info_sup;

        return $this;
    }

    public function getPouvoirs(): ?string
    {
        return $this->pouvoirs;
    }

    public function setPouvoirs(?string $pouvoirs): self
    {
        $this->pouvoirs = $pouvoirs;

        return $this;
    }

    public function getFaiblesses(): ?string
    {
        return $this->faiblesses;
    }

    public function setFaiblesses(?string $faiblesses): self
    {
        $this->faiblesses = $faiblesses;

        return $this;
    }

    /**
     * @return Collection|Personnage[]
     */
    public function getPersonnages(): Collection
    {
        return $this->personnages;
    }

    public function addPersonnage(Personnage $personnage): self
    {
        if (!$this->personnages->contains($personnage)) {
            $this->personnages[] = $personnage;
            $personnage->setEspece($this);
        }

        return $this;
    }

    public function removePersonnage(Personnage $personnage): self
    {
        if ($this->personnages->contains($personnage)) {
            $this->personnages->removeElement($personnage);
            // set the owning side to null (unless already changed)
            if ($personnage->getEspece() === $this) {
                $personnage->setEspece(null);
            }
        }

        return $this;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): self
    {
        $this->tag = $tag;

        return $this;
    }
}
