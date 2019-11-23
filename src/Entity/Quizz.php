<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuizzRepository")
 */
class Quizz
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
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_creation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Utilisateur", inversedBy="quizzs")
     */
    private $utilisateur;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\QuestionQuizz", mappedBy="quizz")
     */
    private $questionQuizzs;

    public function __construct()
    {
        $this->questionQuizzs = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

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

    /**
     * @return Collection|QuestionQuizz[]
     */
    public function getQuestionQuizzs(): Collection
    {
        return $this->questionQuizzs;
    }

    public function addQuestionQuizz(QuestionQuizz $questionQuizz): self
    {
        if (!$this->questionQuizzs->contains($questionQuizz)) {
            $this->questionQuizzs[] = $questionQuizz;
            $questionQuizz->setQuizz($this);
        }

        return $this;
    }

    public function removeQuestionQuizz(QuestionQuizz $questionQuizz): self
    {
        if ($this->questionQuizzs->contains($questionQuizz)) {
            $this->questionQuizzs->removeElement($questionQuizz);
            // set the owning side to null (unless already changed)
            if ($questionQuizz->getQuizz() === $this) {
                $questionQuizz->setQuizz(null);
            }
        }

        return $this;
    }
}
