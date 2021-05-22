<?php

namespace App\Twig;

use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\Session\Session;

class DiversExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return array(
            new TwigFilter('bool', array(
                $this,
                'bool'
            )),
            new TwigFilter('unbool', array(
                $this,
                'unbool'
            )),
            new TwigFilter('sexe', array(
                $this,
                'sexe'
            ))
        );
    }

    public function getFunctions(): array
    {
        return array(
            new TwigFunction('arborescence', array(
                $this,
                'arborescence'
            )),
            new TwigFunction('popup', array(
                $this,
                'getPopup'
            )),
            new TwigFunction('select', array(
                $this,
                'getSelect'
            )),
            new TwigFunction('isVo', array(
                $this,
                'getVO'
            )),
            new TwigFunction('photo', array(
                $this,
                'getPhoto'
            )),
            new TwigFunction('modifier', array(
                $this,
                'boutonModifier'
            )),
            new TwigFunction('ajouter', array(
                $this,
                'boutonAjouter'
            )),
            new TwigFunction('supprimer', array(
                $this,
                'boutonSupprimer'
            ))
        );
    }

    /**
     * Arborescence d'une page
     * 
     * @param array $paths
     * @return string
     */
    public function arborescence(array $paths)
    {
        if(!isset($paths['paths'])){
            $paths['paths'] = array();
        }
        
        if(!isset($paths['paths'])){
            $paths['paths'] = array();
        }
        
        $return = "<nav aria-label='breadcrumb'>";
        $return .= "<ol class='breadcrumb'>";
        $return .= "<li class='breadcrumb-item'><a href='" . $paths['home'] . "'><i class='fas fa-home'></i></a></li>";
        
        foreach ($paths['paths'] as $url => $label) {
            $return .= "<li class='breadcrumb-item'><a href='$url'>$label</a></li>";
        }
        
        if(isset($paths['active'])){
            $return .= "<li class='breadcrumb-item active' aria-current='page'>" . $paths['active'] . "</li>";
        }
        $return .= "</ol></nav>";
        
        return $return;
    }
    
    /**
     * Retourne vrai ou faux
     *
     * @param int $value
     * @return string
     */
    public function vrai(int $value)
    {
        return ($value ? 'Vrai' : 'Faux');
    }
    
    /**
     * Retourne oui ou non
     *
     * @param int $value
     * @return string
     */
    public function bool(int $value)
    {
        return ($value ? 'Oui' : 'Non');
    }
    
    /**
     * Retourne oui ou non
     *
     * @param int $value
     * @return string
     */
    public function unbool(int $value)
    {
        return ($value ? 'Non' : 'Oui');
    }
    
    /**
     * Retourne féminin ou masculin
     *
     * @param int $value
     * @return string
     */
    public function sexe(int $value)
    {
        return ($value ? 'Féminin' : 'Masculin');
    }
    
    /**
     * Affichage d'une popup (l'ajout de javascript est nécessaire)
     *
     * @param string $texte
     * @param string $titre
     * @return string
     */
    public function getPopup(string $texte, string $titre = null, $label = '<i class="fas fa-info-circle"></i>')
    {
        $return = '<a tabindex="0" role="button" data-toggle="popover" data-html="true" data-trigger="hover" ' . (!is_null($titre) ? 'title="' . $titre . '"' : '') .' data-content="' . $texte . '">' . $label . '</a>';
        return $return;
    }
    
    /**
     * Retourne un select à partir d'un array
     *
     * @param string $name
     * @param array $values
     * @param array $params
     * @return string
     */
    public function getSelect(string $name, array $values, array $params = array()){
        
        $opt = array();
        $opt['default'] = "";
        $opt['null'] = false;
        $opt['id'] = $name;
        $opt['class'] = "";
        $opt['multiple'] = false;
        
        foreach($params as $option => $valeur){
            if(isset($opt[$option])){
                $opt[$option] = $valeur;
            }
        }
        
        if(!is_array($opt['default'])){
            $opt['default'] = array($opt['default']);
        }
        
        $select = '<select name="' . $name . '" id="' . $opt['id'] . '"';
        
        if($opt['class']){
            $select .= ' class="' . $opt['class'] . '"';
        }
        
        if($opt['multiple']){
            $select .= ' multiple="multiple"';
        }
        
        $select .= ">";
        
        if($opt['null']){
            $select .= '<option value=""></option>';
        }
        
        foreach($values as $index => $value){
            if(is_array($value)){
                $select .= '<optgroup label="' . $index . '">';
                foreach($value as $key => $label){
                    $select .= '<option value="' . $key . '"';
                    if(in_array($key, $opt['default'])){
                        $select .= ' selected="selected"';
                    }
                    $select .= '>' . str_replace("'", "\'", $label) . '</option>';
                }
                $select .= '</optgroup>';
            } else {
                $select .= '<option value="' . $index . '"';
                if(in_array($index, $opt['default'])){
                    $select .= ' selected="selected"';
                }
                $select .= '>' . str_replace("'", "\'", $value) . '</option>';
            }
        }
        
        $select .= '</select>';
        
        return $select;
    }
    
    /**
     * Renvoie la variable vo d'un utilsateur ou 0
     *
     * @param Session $session
     * @return number
     */
    public function getVO($session, $type){
        if($session->get('user')){
            return $session->get('user')[$type . '_vo'];
        } else {
            return 0;
        }
    }
    
    /**
     *
     * @param $element
     * @return string
     */
    public function getPhoto($element, $class = ""){
        $photo = '';
        
        if((new \ReflectionClass($element))->getShortName() == "Photo"){
            $photo  = '<img src="/image/photo/' . $element->getChemin() . '"';
            if($class){
                $photo .= ' class="' . $class . '"';
            }
            $photo .= ' alt="' . $element->getNom(0) . '">';
        } else {
            if(!is_null($element->getPhoto())){
                $photo  = '<img src="/image/photo/' . $element->getPhoto()->getChemin() . '"';
                if($class){
                    $photo .= ' class="' . $class . '"';
                }
                $photo .= ' alt="' . $element->getPhoto()->getNom() . '">';
            }
        }
        return $photo;
    }
    
    /**
     *
     * @param string $path
     * @param string $id
     * @return string
     */
    function boutonAjouter($path, $right = true, $id = ""){
        $return = '<a href="' . $path . '" ';
        if($right){
            $return .= 'class="float-right"';
        }
        if($id){
            $return .= ' id="' . $id . '"';
        } else {
            $return .= ' id="btn-ajouter"';
        }
        $return .= '><i class="fas fa-plus"></i></a>';
        return $return;
    }
    
    /**
     *
     * @param string $path
     * @param string $id
     * @return string
     */
    function boutonModifier($path, $right = true, $id = ""){
        $return  = '<a href="' . $path . '" ';
        if($right){
            $return .= 'class="float-right"';
        }
        if($id){
            $return .= ' id="' . $id . '"';
        }
        $return .= '><i class="fas fa-pen"></i></a>';
        return $return;
    }
    
    /**
     *
     * @param string $path
     * @param string $id
     * @return string
     */
    function boutonSupprimer($path, $right = true, $id = "", $classe = ""){
        $return  = '<a href="' . $path . '" ';
        if($right){
            $return .= 'class="float-right ' . $classe . '"';
        }
        if($id){
            $return .= ' id="' . $id . '"';
        }
        $return .= '><i class="fas fa-times"></i></a>';
        return $return;
    }
}
