<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonnageSerieRepository")
 */
class PersonnageSerie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Personnage", inversedBy="personnageSeries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $personnage;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Serie", inversedBy="personnageSeries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $serie;

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

    public function getSerie(): ?Serie
    {
        return $this->serie;
    }

    public function setSerie(?Serie $serie): self
    {
        $this->serie = $serie;

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
