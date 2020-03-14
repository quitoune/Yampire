<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SerieRepository")
 */
class Serie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150, unique=true)
     */
    private $slug;
    
    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $titre;
    
    /**
     * @ORM\Column(type="string", length=150)
     */
    private $titre_original;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $titre_court;

    /**
     * @ORM\Column(type="integer")
     */
    private $nombre_saison;

    /**
     * @ORM\Column(type="integer")
     */
    private $nombre_episode;

    /**
     * @ORM\Column(type="boolean")
     */
    private $terminee;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Photo", inversedBy="series")
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Saison", mappedBy="serie")
     * @ORM\OrderBy({"numero_saison" = "ASC"})
     */
    private $saisons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Episode", mappedBy="serie")
     * @ORM\OrderBy({"numero_production" = "ASC"})
     */
    private $episodes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PersonnageSerie", mappedBy="serie")
     */
    private $personnageSeries;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag", inversedBy="series")
     */
    private $tag;
    
    public function __construct()
    {
        $this->saisons = new ArrayCollection();
        $this->episodes = new ArrayCollection();
        $this->personnageSeries = new ArrayCollection();
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
    
    public function getTitre(): ?string
    {
        return $this->titre;
    }
    
    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
        
        return $this;
    }
    
    public function getTitreOriginal(): ?string
    {
        return $this->titre_original;
    }
    
    public function setTitreOriginal(string $titre_original): self
    {
        $this->titre_original = $titre_original;
        
        return $this;
    }

    public function getTitreCourt(): ?string
    {
        return $this->titre_court;
    }

    public function setNomCourt(?string $titre_court): self
    {
        $this->titre_court = $titre_court;

        return $this;
    }

    public function getNom($is_vo = 0){
        if(!$is_vo && !is_null($this->titre)){
            return $this->titre;
        } else {
            return $this->titre_original;
        }
    }
    
    public function getNombreSaison(): ?int
    {
        return $this->nombre_saison;
    }

    public function setNombreSaison(int $nombre_saison): self
    {
        $this->nombre_saison = $nombre_saison;

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

    public function getTerminee(): ?bool
    {
        return $this->terminee;
    }

    public function setTerminee(bool $terminee): self
    {
        $this->terminee = $terminee;

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
     * @return Collection|Saison[]
     */
    public function getSaisons(): Collection
    {
        return $this->saisons;
    }

    public function addSaison(Saison $saison): self
    {
        if (!$this->saisons->contains($saison)) {
            $this->saisons[] = $saison;
            $saison->setSerie($this);
        }

        return $this;
    }

    public function removeSaison(Saison $saison): self
    {
        if ($this->saisons->contains($saison)) {
            $this->saisons->removeElement($saison);
            // set the owning side to null (unless already changed)
            if ($saison->getSerie() === $this) {
                $saison->setSerie(null);
            }
        }

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
            $episode->setSerie($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): self
    {
        if ($this->episodes->contains($episode)) {
            $this->episodes->removeElement($episode);
            // set the owning side to null (unless already changed)
            if ($episode->getSerie() === $this) {
                $episode->setSerie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PersonnageSerie[]
     */
    public function getPersonnageSeries(): Collection
    {
        return $this->personnageSeries;
    }

    public function addPersonnageSeries(PersonnageSerie $personnageSeries): self
    {
        if (!$this->personnageSeries->contains($personnageSeries)) {
            $this->personnageSeries[] = $personnageSeries;
            $personnageSeries->setSerie($this);
        }

        return $this;
    }

    public function removePersonnageSeries(PersonnageSerie $personnageSeries): self
    {
        if ($this->personnageSeries->contains($personnageSeries)) {
            $this->personnageSeries->removeElement($personnageSeries);
            // set the owning side to null (unless already changed)
            if ($personnageSeries->getSerie() === $this) {
                $personnageSeries->setSerie(null);
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
