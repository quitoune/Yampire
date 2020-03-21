<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Controller\QuestionController;
use App\Controller\AppController;
use App\Entity\Question;
use App\Entity\Citation;
use App\Entity\Serie;
use App\Entity\Personnage;

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
            new TwigFunction('select_vrai_faux', array(
                $this,
                'getSelectVraiFaux'
            )),
            new TwigFunction('select_episode', array(
                $this,
                'getSelectEpisode'
            )),
            new TwigFunction('select_correction', array(
                $this,
                'getSelectCorrection'
            )),
            new TwigFunction('select_type_question', array(
                $this,
                'selectTypeQuestion'
            )),
            new TwigFunction('select_type_proposition', array(
                $this,
                'selectTypeProposition'
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
     * Récupération des types de proposition
     *
     * @param int $key
     * @return int|string
     */
    public function selectTypeProposition(string $name, string $id = "")
    {
        if(!$id){
            $id = $name;
        }
        $select = '<select id="' . $id . '" name="' . $name . '" class="form-control">';
        foreach (QuestionController::TYPE_PROPOSITION as $index => $valeur){
            $select .= '<option value="' . $index .  '">' . $valeur . '</option>';
        }
        $select .= '</select>';
        return $select;
    }
    
    /**
     * Récupération des types de question
     *
     * @param int $key
     * @return int|string
     */
    public function selectTypeQuestion(string $name, string $id = "")
    {
        if(!$id){
            $id = $name;
        }
        $select = '<select id="' . $id . '" name="' . $name . '" class="form-control">';
        foreach (QuestionController::TYPE_QUESTION as $index => $valeur){
            $select .= '<option value="' . $index .  '">' . $valeur . '</option>';
        }
        $select .= '</select>';
        return $select;
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
     * Retourne une liste déroulante Vrai/Faux
     * 
     * @param string $id
     * @param string $name
     */
    public function getSelectVraiFaux(string $id, string $name = ""){
        if($name == ""){
            $id = $name;
        }
        
        $select  = "<select id = '" . $id . "' name = '" . $name . "' class = 'form-control'>";
        $select .= "<option value='1'>Vrai</option>";
        $select .= "<option value='0'>Faux</option>";
        $select .= "</select>";
        
        return $select;
    }
    
    /**
     * 
     * @param $doctrine
     * @param string $id
     * @param string $classe
     * @param string $name
     * @return string
     */
    public function getSelectEpisode($doctrine, string $id, int $reponse, string $classe = "", string $name = ""){
        $series = $doctrine->getRepository(Serie::class)->findAll();
        if($name == ""){
            $name = $id;
        }
        
        $select  = "<select id = '" . $id . "' data-reponse='" . $reponse . "' name = '" . $name . "' class = 'form-control";
        if($classe){
            $select .= " " . $classe;
        }
        $select .= "'>";
        
        foreach ($series as $serie){
            $select .= "<optgroup label='" . $serie->getNom() . "'>";
            foreach ($serie->getSaisons() as $saison){
                $select .= "<optgroup label='&nbsp;&nbsp;&nbsp;&nbsp;Saison " . $saison->getNumeroSaison() . "'>";
                foreach($saison->getEpisodes() as $episode){
                    $select .= "<option value='" . $episode->getId() . "'>";
                    $select .= "&nbsp;&nbsp;&nbsp;&nbsp;" . $episode->getNumeroEpisode() . ". " . $episode->getTitreOriginal();
                    $select .= "</option>";
                }
                $select .= "</optgroup>";
            }
            $select .= "</optgroup>";
        }
        
        $select .= "</select>";
        
        return $select;
    }
    
    /**
    *
    * @param $doctrine
    * @param string $id
    * @param string $classe
    * @param string $name
    * @return string
    */
    public function getSelectPersonnage($doctrine, string $id, int $reponse, string $classe = "", string $name = ""){
        $personnages = $doctrine->getRepository(Personnage::class)->findBy(array(), array('nom'=> 'ASC', 'prenom' => 'ASC'));
        
        if($name == ""){
            $name = $id;
        }
        
        $select  = "<select id = '" . $id . "' data-reponse='" . $reponse . "' name = '" . $name . "' class = 'form-control";
        if($classe){
            $select .= " " . $classe;
        }
        $select .= "'>";
        $select .= "<option></option>";
        foreach ($personnages as $perso){
            $select .= "<option value='" . $perso->getId() . "'>" . $perso->getNomUsage() . "</option>";
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
    public function afficherQuestion(Question $question, int $ordre = 0, $doctrine)
    {
        switch($question->getTypeQuestion()){
            case 7:
                return $this->afficherVraiFaux($question, $ordre);
                break;
            case 2:
                return $this->afficherCitation($question->getCitation(), $ordre, $doctrine);
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
     * @param $doctrine
     * @return string
     */
    public function afficherCitation(Citation $citation = null, int $ordre = 0, $doctrine)
    {
        $return = "";
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
            
            $return .= '<div class="row">';
            
            $return .= '<div class="col-lg-6 col-md-6 col-12">';
            $return .= '<label for="personnage_' . $id . '" style="font-size: larger;">Qui la prononce ?</label>';
            $return .= '<div class="col-lg-12 col-md-12 col-12">';
            $return .= '<div class="form-group w-100">';
            $return .= '<label for="from_personnage_' . $id . '">Auteur</label>';
            $return .= $this->getSelectPersonnage($doctrine, "from_personnage_" . $id, (is_null($citation->getFromPersonnage()) ? '' : $citation->getFromPersonnage()->getId()), "", "citation[" . $id . "][from_personnage]");
            $return .= '</div>';
            $return .= '</div>';
            
            $return .= '<div class="col-lg-12 col-md-12 col-12">';
            $return .= '<label for="moment_' . $id . '" style="font-size: larger;">Quand est-elle dit ?</label>';
            $return .= '<div class="form-group w-100">';
            $return .= '<label for="episode_' . $id . '">Episode</label>';
            $return .= $this->getSelectEpisode($doctrine, "episode_" . $id, $citation->getEpisode()->getId(), "", "citation[" . $id . "][episode]");
            $return .= '</div>';
            $return .= '</div>';
            $return .= '</div>';
            
            $return .= '<div class="col-lg-6 col-md-6 col-12">';
            $return .= '<label for="destinataire_' . $id . '" style="font-size: larger;">Quels sont les destinataires ?</label>';
            $return .= '<div class="col-lg-12 col-md-12 col-12">';
            $return .= '<div class="form-group w-100">';
            $return .= '<label for="to_personnage_1_' . $id . '">Destinataire 1</label>';
            $return .= $this->getSelectPersonnage($doctrine, "to_personnage_1_" . $id, (is_null($citation->getToPersonnage1()) ? -1 : $citation->getToPersonnage1()->getId()), "", "citation[" . $id . "][to_personnage_1]");
            $return .= '</div>';
            $return .= '</div>';
            
            $return .= '<div class="col-lg-12 col-md-12 col-12">';
            $return .= '<div class="form-group w-100">';
            $return .= '<label for="to_personnage_2_' . $id . '">Destinataire 2</label>';
            $return .= $this->getSelectPersonnage($doctrine, "to_personnage_" . $id, (is_null($citation->getToPersonnage2()) ? -1 : $citation->getToPersonnage2()->getId()), "", "citation[" . $id . "][to_personnage_2]");
            $return .= '</div>';
            $return .= '</div>';
            
            $return .= '<div class="col-lg-12 col-md-12 col-12">';
            $return .= '<div class="form-group w-100">';
            $return .= '<label for="to_personnage_' . $id . '">Destinataire Autre</label>';
            $return .= '<input type="text" class="form-control" data-reponse="' . $citation->getToPersonnage() . '" name="citation[' . $id . '][to_personnage]" id="to_personnage_' . $id . '">';
            $return .= '</div>';
            $return .= '</div>';
            $return .= '</div>';
            
            $return .= '</div>';
            $return .= '</div>';
            
            $return .= '<div class="card-footer">';
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
        
        $return .= '<div class="form-check" data-proposition="1"';
        if($question->getReponse()){
            $return .= ' data-reponse="1"';
        } else {
            $return .= ' data-reponse="0"';
        }
        $return .= '>';
        $return .= '<label class="container">Vrai';
        $return .= '<span class="red hidden"><i class="fas fa-times"></i></span>';
        $return .= '<span class="green hidden"><i class="fas fa-check"></i></span>';
        $return .= '<input type="radio" name="question_quizz_' . $id . '" id="question_quizz_' . $id . '" value="1">';
        $return .= '<span class="checkmark"></span>';
        $return .= '</label>';
        
        
        $return .= '</div>';
        
        $return .= '<div class="form-check" data-proposition="2"';
        if($question->getReponse()){
            $return .= ' data-reponse="0"';
        } else {
            $return .= ' data-reponse="1"';
        }
        $return .= '>';
        $return .= '<label class="container">Faux';
        $return .= '<span class="red hidden"><i class="fas fa-times"></i></span>';
        $return .= '<span class="green hidden"><i class="fas fa-check"></i></span>';
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
                $return .= '<span class="red hidden"><i class="fas fa-times"></i></span>';
                $return .= '<span class="green hidden"><i class="fas fa-check"></i></span>';
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
