<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\Nationalite;
use App\Entity\Acteur;
use App\Controller\AppController;

class ActeurExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return array(
            new TwigFilter('nationalite_genre', array(
                $this,
                'getNationalite'
            )),
            new TwigFilter('acteur_role', array(
                $this,
                'getActeurRole'
            ))
        );
    }
    
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('select_acteur_role', array(
                $this,
                'getSelectActeurRole'
            )),
            new TwigFunction('nom_acteur', array(
                $this,
                'getNomActeur'
            ))
        );
    }
    
    public function getNationalite(Nationalite $nationalite, int $value)
    {
        return ($value ? $nationalite->getNomFeminin() : $nationalite->getNomMasculin());
    }
    
    /**
     * Renvoie le prénom d'usage ou le prénom d'un personnage
     *
     * @param int $key
     * @return string
     */
    public function getActeurRole(int $key)
    {
        return (isset(AppController::ACTEUR_PERSONNAGE_ROLES[$key]) ? AppController::ACTEUR_PERSONNAGE_ROLES[$key] : $key);
    }
    
    /**
     * renvoie un select des rôles
     * @param string $id
     * @return string
     */
    public function getSelectActeurRole(string $id, string $name = "", int $principal = 1){
        if($name == ""){
            $id = $name;
        }
        
        $select = "<select id = '" . $id . "' name = '" . $name . "' class = 'form-control'>";
        
        foreach(AppController::ACTEUR_PERSONNAGE_ROLES as $key => $role){
            $select .= "<option value = '" . $key . "' " . ($principal == $key ? "selected" : "") . " >" . $role . "</option>";
        }
        $select .= "</select>";
        
        return $select;
    }
    
    /**
     * Renvoi le nom d'un personnage en fonction de ses informations
     *
     * @param Acteur $acteur
     * @return string
     */
    public function getNomActeur(Acteur $acteur)
    {
        $nom_complet = $acteur->getPrenom();
        if(!is_null($acteur->getNom())){
            $nom_complet .= ' ' . $acteur->getNom();
        }
        
        return $nom_complet;
    }
}
