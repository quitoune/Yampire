<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type_question;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type_proposition;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $intitule;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $reponse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $proposition_1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $proposition_2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $proposition_3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $proposition_4;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $proposition_5;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $explication;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Citation")
     */
    private $citation;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $date_creation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Utilisateur", inversedBy="questions")
     */
    private $utilisateur;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\QuestionQuizz", mappedBy="question")
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

    public function getTypeQuestion(): ?int
    {
        return $this->type_question;
    }

    public function setTypeQuestion(int $type_question): self
    {
        $this->type_question = $type_question;

        return $this;
    }

    public function getTypeProposition(): ?int
    {
        return $this->type_proposition;
    }

    public function setTypeProposition(int $type_proposition): self
    {
        $this->type_proposition = $type_proposition;

        return $this;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(?string $intitule): self
    {
        $this->intitule = $intitule;

        return $this;
    }

    public function getReponse(): ?int
    {
        return $this->reponse;
    }

    public function setReponse(?int $reponse): self
    {
        $this->reponse = $reponse;

        return $this;
    }

    public function getProposition1(): ?string
    {
        return $this->proposition_1;
    }

    public function setProposition1(?string $proposition_1): self
    {
        $this->proposition_1 = $proposition_1;

        return $this;
    }

    public function getProposition2(): ?string
    {
        return $this->proposition_2;
    }

    public function setProposition2(?string $proposition_2): self
    {
        $this->proposition_2 = $proposition_2;

        return $this;
    }

    public function getProposition3(): ?string
    {
        return $this->proposition_3;
    }

    public function setProposition3(?string $proposition_3): self
    {
        $this->proposition_3 = $proposition_3;

        return $this;
    }

    public function getProposition4(): ?string
    {
        return $this->proposition_4;
    }

    public function setProposition4(?string $proposition_4): self
    {
        $this->proposition_4 = $proposition_4;

        return $this;
    }

    public function getProposition5(): ?string
    {
        return $this->proposition_5;
    }

    public function setProposition5(?string $proposition_5): self
    {
        $this->proposition_5 = $proposition_5;

        return $this;
    }
    
    public function getExplication(): ?string
    {
        return $this->explication;
    }
    
    public function setExplication(?string $explication): self
    {
        $this->explication = $explication;
        
        return $this;
    }

    public function getCitation(): ?Citation
    {
        return $this->citation;
    }

    public function setCitation(?Citation $citation): self
    {
        $this->citation = $citation;

        return $this;
    }
    
    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }
    
    public function setDateCreation(\DateTimeInterface $date_creation): self
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
            $questionQuizz->setQuestion($this);
        }

        return $this;
    }

    public function removeQuestionQuizz(QuestionQuizz $questionQuizz): self
    {
        if ($this->questionQuizzs->contains($questionQuizz)) {
            $this->questionQuizzs->removeElement($questionQuizz);
            // set the owning side to null (unless already changed)
            if ($questionQuizz->getQuestion() === $this) {
                $questionQuizz->setQuestion(null);
            }
        }

        return $this;
    }
}
