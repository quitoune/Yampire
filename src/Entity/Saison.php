<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SaisonRepository")
 */
class Saison
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero_saison;

    /**
     * @ORM\Column(type="integer")
     */
    private $nombre_episode;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Serie", inversedBy="saisons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $serie;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Photo", inversedBy="saisons")
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Episode", mappedBy="saison")
     * @ORM\OrderBy({"numero_episode" = "ASC"})
     */
    private $episodes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PersonnageSaison", mappedBy="saison")
     */
    private $personnageSaisons;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag", inversedBy="saisons")
     */
    private $tag;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
        $this->personnageSaisons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroSaison(): ?int
    {
        return $this->numero_saison;
    }

    public function setNumeroSaison(int $numero_saison): self
    {
        $this->numero_saison = $numero_saison;

        return $this;
    }

    public function getNombreEpisode(): ?int
    {
        return $this->nombre_episode;
    }

    public function setNombreEpisode(int $nombre_episode): self
    {
        $this->nombre_episode = $nombre_episode;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getPhoto(): ?Photo
    {
        return $this->photo;
    }

    public function setPhoto(?Photo $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @return Collection|Episode[]
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode): self
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->setSaison($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): self
    {
        if ($this->episodes->contains($episode)) {
            $this->episodes->removeElement($episode);
            // set the owning side to null (unless already changed)
            if ($episode->getSaison() === $this) {
                $episode->setSaison(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PersonnageSaison[]
     */
    public function getPersonnageSaisons(): Collection
    {
        return $this->personnageSaisons;
    }

    public function addPersonnageSaison(PersonnageSaison $personnageSaison): self
    {
        if (!$this->personnageSaisons->contains($personnageSaison)) {
            $this->personnageSaisons[] = $personnageSaison;
            $personnageSaison->setSaison($this);
        }

        return $this;
    }

    public function removePersonnageSaison(PersonnageSaison $personnageSaison): self
    {
        if ($this->personnageSaisons->contains($personnageSaison)) {
            $this->personnageSaisons->removeElement($personnageSaison);
            // set the owning side to null (unless already changed)
            if ($personnageSaison->getSaison() === $this) {
                $personnageSaison->setSaison(null);
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
