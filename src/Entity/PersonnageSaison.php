<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonnageSaisonRepository")
 */
class PersonnageSaison
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Personnage", inversedBy="personnageSaisons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $personnage;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Saison", inversedBy="personnageSaisons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $saison;

    /**
     * @ORM\Column(type="smallint")
     */
    private $principal;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersonnage(): ?Personnage
    {
        return $this->personnage;
    }

    public function setPersonnage(?Personnage $personnage): self
    {
        $this->personnage = $personnage;

        return $this;
    }

    public function getSaison(): ?Saison
    {
        return $this->saison;
    }

    public function setSaison(?Saison $saison): self
    {
        $this->saison = $saison;

        return $this;
    }

    public function getPrincipal(): ?int
    {
        return $this->principal;
    }

    public function setPrincipal(int $principal): self
    {
        $this->principal = $principal;

        return $this;
    }
}
