<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CitationRepository")
 */
class Citation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $texte;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $to_personnage;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_creation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Personnage", inversedBy="citations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $from_personnage;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Personnage", inversedBy="citations_recu")
     */
    private $to_personnage_1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Personnage", inversedBy="quotes_recu")
     */
    private $to_personnage_2;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Episode", inversedBy="citations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $episode;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Utilisateur", inversedBy="citations")
     */
    private $utilisateur;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): self
    {
        $this->texte = $texte;

        return $this;
    }

    public function getToPersonnage(): ?string
    {
        return $this->to_personnage;
    }

    public function setToPersonnage(?string $to_personnage): self
    {
        $this->to_personnage = $to_personnage;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getFromPersonnage(): ?Personnage
    {
        return $this->from_personnage;
    }

    public function setFromPersonnage(?Personnage $from_personnage): self
    {
        $this->from_personnage = $from_personnage;

        return $this;
    }

    public function getToPersonnage1(): ?Personnage
    {
        return $this->to_personnage_1;
    }

    public function setToPersonnage1(?Personnage $to_personnage_1): self
    {
        $this->to_personnage_1 = $to_personnage_1;

        return $this;
    }

    public function getToPersonnage2(): ?Personnage
    {
        return $this->to_personnage_2;
    }

    public function setToPersonnage2(?Personnage $to_personnage_2): self
    {
        $this->to_personnage_2 = $to_personnage_2;

        return $this;
    }

    public function getEpisode(): ?Episode
    {
        return $this->episode;
    }

    public function setEpisode(?Episode $episode): self
    {
        $this->episode = $episode;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}
