<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UtilisateurRepository")
 * @UniqueEntity(
 *     fields={"username"},
 *     errorPath="username",
 *     message="Le pseudo utilisé est déjà utilisé"
 * )
 */
class Utilisateur
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
    private $username;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="text")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $vo;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $salt;

    /**
     * @ORM\Column(type="text")
     */
    private $question_1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reponse_1;

    /**
     * @ORM\Column(type="text")
     */
    private $question_2;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reponse_2;

    /**
     * @ORM\Column(type="smallint")
     */
    private $correction;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbr_max;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbr_max_ajax;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Chanson", mappedBy="utilisateur")
     */
    private $chansons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Citation", mappedBy="utilisateur")
     */
    private $citations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="utilisateur")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Quizz", mappedBy="utilisateur")
     */
    private $quizzs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Question", mappedBy="utilisateur")
     */
    private $questions;

    public function __construct()
    {
        $this->chansons = new ArrayCollection();
        $this->citations = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->quizzs = new ArrayCollection();
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getVo(): ?bool
    {
        return $this->vo;
    }

    public function setVo(bool $vo): self
    {
        $this->vo = $vo;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getQuestion1(): ?string
    {
        return $this->question_1;
    }

    public function setQuestion1(string $question_1): self
    {
        $this->question_1 = $question_1;

        return $this;
    }

    public function getResponse1(): ?string
    {
        return $this->reponse_1;
    }

    public function setReponse1(string $reponse_1): self
    {
        $this->reponse_1 = $reponse_1;

        return $this;
    }

    public function getQuestion2(): ?string
    {
        return $this->question_2;
    }

    public function setQuestion2(string $question_2): self
    {
        $this->question_2 = $question_2;

        return $this;
    }

    public function getReponse2(): ?string
    {
        return $this->reponse_2;
    }

    public function setReponse2(string $reponse_2): self
    {
        $this->reponse_2 = $reponse_2;

        return $this;
    }

    public function getCorrection(): ?int
    {
        return $this->correction;
    }

    public function setCorrection(int $correction): self
    {
        $this->correction = $correction;

        return $this;
    }

    public function getNbrMax(): ?int
    {
        return $this->nbr_max;
    }

    public function setNbrMax(int $nbr_max): self
    {
        $this->nbr_max = $nbr_max;

        return $this;
    }

    public function getNbrMaxAjax(): ?int
    {
        return $this->nbr_max_ajax;
    }

    public function setNbrMaxAjax(int $nbr_max_ajax): self
    {
        $this->nbr_max_ajax = $nbr_max_ajax;

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
            $chanson->setUtilisateur($this);
        }

        return $this;
    }

    public function removeChanson(Chanson $chanson): self
    {
        if ($this->chansons->contains($chanson)) {
            $this->chansons->removeElement($chanson);
            // set the owning side to null (unless already changed)
            if ($chanson->getUtilisateur() === $this) {
                $chanson->setUtilisateur(null);
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
            $citation->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCitation(Citation $citation): self
    {
        if ($this->citations->contains($citation)) {
            $this->citations->removeElement($citation);
            // set the owning side to null (unless already changed)
            if ($citation->getUtilisateur() === $this) {
                $citation->setUtilisateur(null);
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
            $note->setUtilisateur($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getUtilisateur() === $this) {
                $note->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Quizz[]
     */
    public function getQuizzs(): Collection
    {
        return $this->quizzs;
    }

    public function addQuizz(Quizz $quizz): self
    {
        if (!$this->quizzs->contains($quizz)) {
            $this->quizzs[] = $quizz;
            $quizz->setUtilisateur($this);
        }

        return $this;
    }

    public function removeQuizz(Quizz $quizz): self
    {
        if ($this->quizzs->contains($quizz)) {
            $this->quizzs->removeElement($quizz);
            // set the owning side to null (unless already changed)
            if ($quizz->getUtilisateur() === $this) {
                $quizz->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setUtilisateur($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getUtilisateur() === $this) {
                $question->setUtilisateur(null);
            }
        }

        return $this;
    }
}
