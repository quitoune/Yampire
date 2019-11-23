<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 */
class Tag
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Photo", mappedBy="tag")
     */
    private $photos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Espece", mappedBy="tag")
     */
    private $especes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Acteur", mappedBy="tag")
     */
    private $acteurs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Episode", mappedBy="tag")
     */
    private $episodes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Personnage", mappedBy="tag")
     */
    private $personnages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Serie", mappedBy="tag")
     */
    private $series;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Saison", mappedBy="tag")
     */
    private $saisons;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->especes = new ArrayCollection();
        $this->acteurs = new ArrayCollection();
        $this->episodes = new ArrayCollection();
        $this->personnages = new ArrayCollection();
        $this->series = new ArrayCollection();
        $this->saisons = new ArrayCollection();
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

    /**
     * @return Collection|Photo[]
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->addTag($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): self
    {
        if ($this->photos->contains($photo)) {
            $this->photos->removeElement($photo);
            $photo->removeTag($this);
        }

        return $this;
    }

    /**
     * @return Collection|Espece[]
     */
    public function getEspeces(): Collection
    {
        return $this->especes;
    }

    public function addEspece(Espece $espece): self
    {
        if (!$this->especes->contains($espece)) {
            $this->especes[] = $espece;
            $espece->setTag($this);
        }

        return $this;
    }

    public function removeEspece(Espece $espece): self
    {
        if ($this->especes->contains($espece)) {
            $this->especes->removeElement($espece);
            // set the owning side to null (unless already changed)
            if ($espece->getTag() === $this) {
                $espece->setTag(null);
            }
        }

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
            $acteur->setTag($this);
        }

        return $this;
    }

    public function removeActeur(Acteur $acteur): self
    {
        if ($this->acteurs->contains($acteur)) {
            $this->acteurs->removeElement($acteur);
            // set the owning side to null (unless already changed)
            if ($acteur->getTag() === $this) {
                $acteur->setTag(null);
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
            $episode->setTag($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): self
    {
        if ($this->episodes->contains($episode)) {
            $this->episodes->removeElement($episode);
            // set the owning side to null (unless already changed)
            if ($episode->getTag() === $this) {
                $episode->setTag(null);
            }
        }

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
            $personnage->setTag($this);
        }

        return $this;
    }

    public function removePersonnage(Personnage $personnage): self
    {
        if ($this->personnages->contains($personnage)) {
            $this->personnages->removeElement($personnage);
            // set the owning side to null (unless already changed)
            if ($personnage->getTag() === $this) {
                $personnage->setTag(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Serie[]
     */
    public function getSeries(): Collection
    {
        return $this->series;
    }

    public function addSeries(Serie $series): self
    {
        if (!$this->series->contains($series)) {
            $this->series[] = $series;
            $series->setTag($this);
        }

        return $this;
    }

    public function removeSeries(Serie $series): self
    {
        if ($this->series->contains($series)) {
            $this->series->removeElement($series);
            // set the owning side to null (unless already changed)
            if ($series->getTag() === $this) {
                $series->setTag(null);
            }
        }

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
            $saison->setTag($this);
        }

        return $this;
    }

    public function removeSaison(Saison $saison): self
    {
        if ($this->saisons->contains($saison)) {
            $this->saisons->removeElement($saison);
            // set the owning side to null (unless already changed)
            if ($saison->getTag() === $this) {
                $saison->setTag(null);
            }
        }

        return $this;
    }
}
