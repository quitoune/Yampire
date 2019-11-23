<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Controller\AppController;
use App\Entity\Personnage;

class PersonnageExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return array(
            new TwigFilter('sexe', array(
                $this,
                'getSexe'
            )),
            new TwigFilter('role', array(
                $this,
                'getRole'
            ))
        );
    }
    
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('nom_complet', array(
                $this,
                'getNomComplet'
            )),
            new TwigFunction('nom_usage', array(
                $this,
                'getNomUsage'
            )),
            new TwigFunction('select_role', array(
                $this,
                'getSelectRole'
            )),
            new TwigFunction('radio_role', array(
                $this,
                'getRadioRole'
            ))
        );
    }
    
    public function getSexe($value)
    {
        return ($value ? 'Féminin' : 'Masculin');
    }
    
    /**
     * Renvoie le rôle d'un personnage dans une série
     *
     * @param int $key
     * @return string
     */
    public function getRole(int $key)
    {
        return (isset(AppController::PERSONNAGE_SERIE_ROLES[$key]) ? AppController::PERSONNAGE_SERIE_ROLES[$key] : $key);
    }
    
    /**
     * Renvoi le nom d'un personnage en fonction de ses informations
     *
     * @param Personnage $personnage
     * @return string
     */
    public function getNomUsage(Personnage $personnage)
    {
        if (is_null($personnage->getPrenomUsage())) {
            $nom_complet = $personnage->getPrenom();
        } else {
            $nom_complet = $personnage->getPrenomUsage();
        }
        $nom_complet .= ($personnage->getNom() ? ' ' : '') . $personnage->getNom();
        
        return $nom_complet;
    }
    
    /**
     * Renvoi le nom d'un personnage en fonction de ses informations
     *
     * @param Personnage $personnage
     * @return string
     */
    public function getNomComplet(Personnage $personnage)
    {
        $nom_complet = "";
        if (!is_null($personnage->getPrenom())) {
            $nom_complet .= $personnage->getPrenom();
        }
        if(!is_null($personnage->getPrenomUsage())) {
            if(is_null($personnage->getPrenom())){
                $nom_complet .= $personnage->getPrenomUsage();
            } else {
                $nom_complet .= ' "' . $personnage->getPrenomUsage() . '"';
            }
        }
        if(!is_null($personnage->getNom())){
            $nom_complet .= ' ' . $personnage->getNom();
        }
        
        return trim($nom_complet);
    }
    
    /**
     * renvoie un select des rôles des personnages dans une série ou une saison
     * @param string $id
     * @return string
     */
    public function getSelectRole(string $id, string $name = ""){
        if($name == ""){
            $name = $id;
        }
        
        $select = "<select id = '" . $id . "' name = '" . $name . "' class = 'form-control'>";
        
        foreach(AppController::PERSONNAGE_SERIE_ROLES as $key => $role){
            $select .= "<option value = '" . $key . "' " . ($key == 1 ? "selected" : "") . " >" . $role . "</option>";
        }
        $select .= "</select>";
        
        return $select;
    }
    
    /**
     *
     * @param string $name
     * @param string $id
     * @return string
     */
    public function getRadioRole(string $name, string $id = ""){
        $radio = "";
        
        foreach(AppController::PERSONNAGE_SERIE_ROLES as $key => $role){
            $radio .= "<input type='radio' value='" . $key . "' name = '" . $name . "'";
            if($id){
                $radio .= " id = '" . $id . "'";
            }
            $radio .= "> " . $role . " ";
        }
        return $radio;
    }
}
