<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Controller\QuestionController;
use App\Controller\AppController;
use App\Entity\Question;
use App\Entity\Citation;

class QuestionExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return array(
            new TwigFilter('type_proposition', array(
                $this,
                'getTypeProposition'
            )),
            new TwigFilter('type_question', array(
                $this,
                'getTypeQuestion'
            )),
            new TwigFilter('correction', array(
                $this,
                'getCorrection'
            ))
        );
    }

    public function getFunctions(): array
    {
        return array(
            new TwigFunction('afficher', array(
                $this,
                'afficherQuestion'
            )),
            new TwigFunction('select_correction', array(
                $this,
                'getSelectCorrection'
            ))
        );
    }
    
    /**
     * Récupération des types de proposition
     *
     * @param int $key
     * @return int|string
     */
    public function getTypeProposition(int $key)
    {
        return (isset(QuestionController::TYPE_PROPOSITION[$key]) ? QuestionController::TYPE_PROPOSITION[$key] : $key);
    }
    
    /**
     * Récupération des types de question
     *
     * @param int $key
     * @return int|string
     */
    public function getTypeQuestion(int $key)
    {
        return (isset(QuestionController::TYPE_QUESTION[$key]) ? QuestionController::TYPE_QUESTION[$key] : $key);
    }
    
    /**
     * Renvoie le type de correction
     *
     * @param int $key
     * @return string
     */
    public function getCorrection(int $key)
    {
        return (isset(AppController::CORRECTION[$key]) ? AppController::CORRECTION[$key] : $key);
    }
    
    /**
     * renvoie un select des types de correction
     * @param string $id
     * @return string
     */
    public function getSelectCorrection(string $id, string $name = ""){
        if($name == ""){
            $id = $name;
        }
        
        $select = "<select id = '" . $id . "' name = '" . $name . "' class = 'form-control'>";
        
        foreach(AppController::CORRECTION as $key => $role){
            $select .= "<option value = '" . $key . "' " . ($key == 1 ? "selected" : "") . " >" . $role . "</option>";
        }
        $select .= "</select>";
        
        return $select;
    }
    
    /**
     * Affichage d'une question
     * 
     * @param Question $question
     * @param int $ordre
     * @return string
     */
    public function afficherQuestion(Question $question, int $ordre = 0)
    {
        switch($question->getTypeQuestion()){
            case 7:
                return $this->afficherVraiFaux($question, $ordre);
                break;
            case 2:
                return $this->afficherCitation($question->getCitation(), $ordre);
                break;
            default:
                return $this->afficherQcm($question, $ordre);
                break;
        }
    }
    
    /**
     * Affichage d'une question de type citation
     *
     * @param Citation $citation
     * @param int $ordre
     * @return string
     */
    public function afficherCitation(Citation $citation = null, int $ordre = 0)
    {
        $return = "OK";
        if(!is_null($citation)){
            $id = $citation->getId();
            
            $return = '<div class="card">';
            $return .= '<div class="card-header">';
            if($ordre){
                $return .= '#' . $ordre;
            } else {
                $return .= '#' . $id;
            }
            $return .= ' <br> <span style="color:blue;font-style:italic;">' . $citation->getTexte() . '</span>';
            $return .= '</div>';
            
            $return .= '<div class="card-body">';
            $return .= 'Qui est l\'auteur de cette citation ? <br>';
            $return .= 'A qui la dit-elle ? <br>';
            $return .= 'Dans quel épisode ? <br>';
            $return .= '</div>';
            
            $return .= '<div class="card-footer">';
            $return .= '</div>';
            $return .= '</div>';
        }
        
        return $return;
    }
    
    /**
     * Affichage d'une question de type citation
     *
     * @param Question $question
     * @param int $ordre
     * @return string
     */
    public function afficherVraiFaux(Question $question, int $ordre = 0)
    {
        $id = $question->getId();
        
        $return = '<div class="card">';
        $return .= '<div class="card-header">';
        if($ordre){
            $return .= '#' . $ordre . ' - ' . $question->getIntitule();
        } else {
            $return .= '#' . $id . ' - ' . $question->getIntitule();
        }
        $return .= '</div>';
        
        $return .= '<div class="card-body">';
        
        $return .= '<div class="form-check">';
        $return .= '<label class="container">Vrai';
        $return .= '<input type="radio" name="question_quizz_' . $id . '" id="question_quizz_' . $id . '" value="1">';
        $return .= '<span class="checkmark"></span>';
        $return .= '</label>';
        
        
        $return .= '</div>';
        
        $return .= '<div class="form-check">';
        $return .= '<label class="container">Faux';
        $return .= '<input type="radio" name="question_quizz_' . $id . '" id="question_quizz_' . $id . '" value="0">';
        $return .= '<span class="checkmark"></span>';
        $return .= '</div>';
        
        $return .= '</div>';
        
        $return .= '<div class="card-footer">';
        $return .= '</div>';
        $return .= '</div>';
        
        return $return;
    }
    
    /**
     * Affichage d'une question de type Question
     *
     * @param Question $question
     * @param int $ordre
     * @return string
     */
    public function afficherQcm(Question $question, int $ordre = 0)
    {
        $id = $question->getId();
        
        $reponse = $question->getReponse();
        
        $return = '<div class="card">';
        $return .= '<div class="card-header">';
        if($ordre){
            $return .= '#' . $ordre . ' - ' . $question->getIntitule();
        } else {
            $return .= '#' . $id . ' - ' . $question->getIntitule();
        }
        $return .= '</div>';
        $return .= '<div class="card-body">';
        for ($i = 1; $i <= 5; $i ++) {
            $methode = 'getProposition' . $i;
            $valeur = $question->{$methode}();
            if (! is_null($valeur)) {
                $return .= '<div class="form-check" data-proposition="' . $i . '"';
                if($i == $reponse){
                    $return .= ' data-reponse="1"';
                } else {
                    $return .= ' data-reponse="0"';
                }
                $return .= '>';
                $return .= '<label class="container">' . $valeur;
                $return .= '<span class="red hidden"><i class="fas fa-times"></i></span><span class="green hidden"><i class="fas fa-check"></i></span>';
                $return .= '<input type="radio" name="quizz_question_' . $id . '" id="quizz_question_' . $id . '" value="' . $i . '">';
                $return .= '<span class="checkmark"></span>';
                $return .= '</label>';
                $return .= '</div>';
            }
        }
        $return .= '</div>';
        $return .= '<div class="card-footer">';
        if (!is_null($question->getExplication())) {
            $return .= '<div class="explication hidden">';
            $return .= $question->getExplication();
            $return .= '</div>';
        }
        $return .= '</div>';
        $return .= '</div>';
        return $return;
    }
}
