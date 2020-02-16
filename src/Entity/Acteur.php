<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActeurRepository")
 */
class Acteur
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
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_naissance;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sexe;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Nationalite", inversedBy="acteurs")
     */
    private $nationalites;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Photo", inversedBy="acteurs")
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ActeurPersonnage", mappedBy="acteur")
     */
    private $acteurPersonnages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="acteur")
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag", inversedBy="acteurs")
     */
    private $tag;

    public function __construct()
    {
        $this->nationalite = new ArrayCollection();
        $this->acteurPersonnages = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->date_naissance;
    }

    public function setDateNaissance(?\DateTimeInterface $date_naissance): self
    {
        $this->date_naissance = $date_naissance;

        return $this;
    }

    public function getSexe(): ?bool
    {
        return $this->sexe;
    }

    public function setSexe(bool $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * @return Collection|Nationalite[]
     */
    public function getNationalites(): Collection
    {
        return $this->nationalites;
    }

    public function addNationalite(Nationalite $nationalite): self
    {
        if (!$this->nationalites->contains($nationalite)) {
            $this->nationalites[] = $nationalite;
        }

        return $this;
    }

    public function removeNationalite(Nationalite $nationalite): self
    {
        if ($this->nationalites->contains($nationalite)) {
            $this->nationalites->removeElement($nationalite);
        }

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
     * @return Collection|ActeurPersonnage[]
     */
    public function getActeurPersonnages(): Collection
    {
        return $this->acteurPersonnages;
    }

    public function addActeurPersonnage(ActeurPersonnage $acteurPersonnage): self
    {
        if (!$this->acteurPersonnages->contains($acteurPersonnage)) {
            $this->acteurPersonnages[] = $acteurPersonnage;
            $acteurPersonnage->setActeur($this);
        }

        return $this;
    }

    public function removeActeurPersonnage(ActeurPersonnage $acteurPersonnage): self
    {
        if ($this->acteurPersonnages->contains($acteurPersonnage)) {
            $this->acteurPersonnages->removeElement($acteurPersonnage);
            // set the owning side to null (unless already changed)
            if ($acteurPersonnage->getActeur() === $this) {
                $acteurPersonnage->setActeur(null);
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
            $note->setActeur($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getActeur() === $this) {
                $note->setActeur(null);
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
