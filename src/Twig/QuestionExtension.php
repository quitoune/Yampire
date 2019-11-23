<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Controller\QuestionController;
use App\Controller\AppController;

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
}
