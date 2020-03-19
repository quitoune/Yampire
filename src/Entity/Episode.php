<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EpisodeRepository")
 */
class Episode
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre_original;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero_episode;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero_production;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $premiere_diffusion;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duree;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Serie", inversedBy="episodes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $serie;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Saison", inversedBy="episodes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $saison;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Personnage", inversedBy="episodes")
     * @ORM\OrderBy({"nom" = "ASC", "prenom" = "ASC"})
     */
    private $personnage;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Chanson", mappedBy="episode")
     */
    private $chansons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Citation", mappedBy="episode")
     */
    private $citations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="episode")
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag", inversedBy="episodes")
     */
    private $tag;

    public function __construct()
    {
        $this->personnage = new ArrayCollection();
        $this->chansons = new ArrayCollection();
        $this->citations = new ArrayCollection();
        $this->notes = new ArrayCollection();
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
    
    public function getNom($is_vo = 0){
        if(!$is_vo && !is_null($this->titre)){
            return $this->titre;
        } else {
            return $this->titre_original;
        }
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

    public function getNumeroEpisode(): ?int
    {
        return $this->numero_episode;
    }

    public function setNumeroEpisode(int $numero_episode): self
    {
        $this->numero_episode = $numero_episode;

        return $this;
    }

    public function getNumeroProduction(): ?int
    {
        return $this->numero_production;
    }

    public function setNumeroProduction(int $numero_production): self
    {
        $this->numero_production = $numero_production;

        return $this;
    }
    
    public function getCodeEpisode($avec_serie = true)
    {
        $code = '';
        if($avec_serie){
            $code .= $this->getSerie()->getTitreCourt() . ' - ';
        }
        $code .= 'S' . $this->getSaison()->getNumeroSaison() . 'E' . $this->numero_episode;
        return $code;
    }

    public function getPremiereDiffusion(): ?\DateTimeInterface
    {
        return $this->premiere_diffusion;
    }

    public function setPremiereDiffusion(?\DateTimeInterface $premiere_diffusion): self
    {
        $this->premiere_diffusion = $premiere_diffusion;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;

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

    public function getSaison(): ?Saison
    {
        return $this->saison;
    }

    public function setSaison(?Saison $saison): self
    {
        $this->saison = $saison;

        return $this;
    }

    /**
     * @return Collection|Personnage[]
     */
    public function getPersonnage(): Collection
    {
        return $this->personnage;
    }

    public function addPersonnage(Personnage $personnage): self
    {
        if (!$this->personnage->contains($personnage)) {
            $this->personnage[] = $personnage;
        }

        return $this;
    }

    public function removePersonnage(Personnage $personnage): self
    {
        if ($this->personnage->contains($personnage)) {
            $this->personnage->removeElement($personnage);
        }

        return $this;
    }

    /**
     * @return Collection|Chanson[]
     */
    public function getChansons(): Collection
    {
        return $this->chansons;
    }

    public function addChanson(Chanson $chanson): self
    {
        if (!$this->chansons->contains($chanson)) {
            $this->chansons[] = $chanson;
            $chanson->setEpisode($this);
        }

        return $this;
    }

    public function removeChanson(Chanson $chanson): self
    {
        if ($this->chansons->contains($chanson)) {
            $this->chansons->removeElement($chanson);
            // set the owning side to null (unless already changed)
            if ($chanson->getEpisode() === $this) {
                $chanson->setEpisode(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Citation[]
     */
    public function getCitations(): Collection
    {
        return $this->citations;
    }

    public function addCitation(Citation $citation): self
    {
        if (!$this->citations->contains($citation)) {
            $this->citations[] = $citation;
            $citation->setEpisode($this);
        }

        return $this;
    }

    public function removeCitation(Citation $citation): self
    {
        if ($this->citations->contains($citation)) {
            $this->citations->removeElement($citation);
            // set the owning side to null (unless already changed)
            if ($citation->getEpisode() === $this) {
                $citation->setEpisode(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setEpisode($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getEpisode() === $this) {
                $note->setEpisode(null);
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
