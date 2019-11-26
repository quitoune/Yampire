<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonnageRepository")
 */
class Personnage
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
    private $prenom_usage;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Espece", inversedBy="personnages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $espece;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Photo", inversedBy="personnages")
     */
    private $photo;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Episode", mappedBy="personnage")
     */
    private $episodes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ActeurPersonnage", mappedBy="personnage")
     */
    private $acteurPersonnages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PersonnageSerie", mappedBy="personnage")
     */
    private $personnageSeries;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PersonnageSaison", mappedBy="personnage")
     */
    private $personnageSaisons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Citation", mappedBy="from_personnage")
     */
    private $citations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Citation", mappedBy="to_personnage_1")
     */
    private $citations_recu;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Citation", mappedBy="to_personnage_2")
     */
    private $quotes_recu;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="personnage")
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag", inversedBy="personnages")
     */
    private $tag;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
        $this->acteurPersonnages = new ArrayCollection();
        $this->personnageSeries = new ArrayCollection();
        $this->personnageSaisons = new ArrayCollection();
        $this->citations = new ArrayCollection();
        $this->citations_recu = new ArrayCollection();
        $this->quotes_recu = new ArrayCollection();
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

    public function getPrenomUsage(): ?string
    {
        return $this->prenom_usage;
    }

    public function setPrenomUsage(?string $prenom_usage): self
    {
        $this->prenom_usage = $prenom_usage;

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
    
    public function getNomComplet(): ?string
    {
        $nom_complet = $this->prenom;
        if(!is_null($this->prenom_usage)){
            $nom_complet .= ' "' . $this->prenom_usage . '"';
        }
        if($this->nom){
            $nom_complet .= " " . $this->nom;
        }
        return $nom_complet;
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

    public function getEspece(): ?Espece
    {
        return $this->espece;
    }

    public function setEspece(?Espece $espece): self
    {
        $this->espece = $espece;

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
            $episode->addPersonnage($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): self
    {
        if ($this->episodes->contains($episode)) {
            $this->episodes->removeElement($episode);
            $episode->removePersonnage($this);
        }

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
            $acteurPersonnage->setPersonnage($this);
        }

        return $this;
    }

    public function removeActeurPersonnage(ActeurPersonnage $acteurPersonnage): self
    {
        if ($this->acteurPersonnages->contains($acteurPersonnage)) {
            $this->acteurPersonnages->removeElement($acteurPersonnage);
            // set the owning side to null (unless already changed)
            if ($acteurPersonnage->getPersonnage() === $this) {
                $acteurPersonnage->setPersonnage(null);
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
            $personnageSeries->setPersonnage($this);
        }

        return $this;
    }

    public function removePersonnageSeries(PersonnageSerie $personnageSeries): self
    {
        if ($this->personnageSeries->contains($personnageSeries)) {
            $this->personnageSeries->removeElement($personnageSeries);
            // set the owning side to null (unless already changed)
            if ($personnageSeries->getPersonnage() === $this) {
                $personnageSeries->setPersonnage(null);
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
            $personnageSaison->setPersonnage($this);
        }

        return $this;
    }

    public function removePersonnageSaison(PersonnageSaison $personnageSaison): self
    {
        if ($this->personnageSaisons->contains($personnageSaison)) {
            $this->personnageSaisons->removeElement($personnageSaison);
            // set the owning side to null (unless already changed)
            if ($personnageSaison->getPersonnage() === $this) {
                $personnageSaison->setPersonnage(null);
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
            $citation->setFromPersonnage($this);
        }

        return $this;
    }

    public function removeCitation(Citation $citation): self
    {
        if ($this->citations->contains($citation)) {
            $this->citations->removeElement($citation);
            // set the owning side to null (unless already changed)
            if ($citation->getFromPersonnage() === $this) {
                $citation->setFromPersonnage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Citation[]
     */
    public function getCitationsRecu(): Collection
    {
        return $this->citations_recu;
    }

    public function addCitationsRecu(Citation $citationsRecu): self
    {
        if (!$this->citations_recu->contains($citationsRecu)) {
            $this->citations_recu[] = $citationsRecu;
            $citationsRecu->setToPersonnage1($this);
        }

        return $this;
    }

    public function removeCitationsRecu(Citation $citationsRecu): self
    {
        if ($this->citations_recu->contains($citationsRecu)) {
            $this->citations_recu->removeElement($citationsRecu);
            // set the owning side to null (unless already changed)
            if ($citationsRecu->getToPersonnage1() === $this) {
                $citationsRecu->setToPersonnage1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Citation[]
     */
    public function getQuotesRecu(): Collection
    {
        return $this->quotes_recu;
    }

    public function addQuotesRecu(Citation $quotesRecu): self
    {
        if (!$this->quotes_recu->contains($quotesRecu)) {
            $this->quotes_recu[] = $quotesRecu;
            $quotesRecu->setToPersonnage2($this);
        }

        return $this;
    }

    public function removeQuotesRecu(Citation $quotesRecu): self
    {
        if ($this->quotes_recu->contains($quotesRecu)) {
            $this->quotes_recu->removeElement($quotesRecu);
            // set the owning side to null (unless already changed)
            if ($quotesRecu->getToPersonnage2() === $this) {
                $quotesRecu->setToPersonnage2(null);
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
            $note->setPersonnage($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getPersonnage() === $this) {
                $note->setPersonnage(null);
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
