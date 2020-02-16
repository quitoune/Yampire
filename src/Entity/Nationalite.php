<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NationaliteRepository")
 */
class Nationalite
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
    private $nom_feminin;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nom_masculin;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Acteur", mappedBy="nationalites")
     */
    private $acteurs;

    public function __construct()
    {
        $this->acteurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomFeminin(): ?string
    {
        return $this->nom_feminin;
    }

    public function setNomFeminin(string $nom_feminin): self
    {
        $this->nom_feminin = $nom_feminin;

        return $this;
    }

    public function getNomMasculin(): ?string
    {
        return $this->nom_masculin;
    }

    public function setNomMasculin(string $nom_masculin): self
    {
        $this->nom_masculin = $nom_masculin;

        return $this;
    }

    /**
     * @return Collection|Acteur[]
     */
    public function getActeurs(): Collection
    {
        return $this->acteurs;
    }

    public function addActeur(Acteur $acteur): self
    {
        if (!$this->acteurs->contains($acteur)) {
            $this->acteurs[] = $acteur;
            $acteur->addNationalite($this);
        }

        return $this;
    }

    public function removeActeur(Acteur $acteur): self
    {
        if ($this->acteurs->contains($acteur)) {
            $this->acteurs->removeElement($acteur);
            $acteur->removeNationalite($this);
        }

        return $this;
    }
}
