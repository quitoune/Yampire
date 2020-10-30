<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Utilisateur;
use App\Entity\Acteur;
use App\Entity\Serie;
use App\Entity\Quizz;
use App\Entity\Personnage;
use App\Entity\Espece;
use App\Entity\Episode;
use App\Entity\Chanson;

class AppController extends AbstractController
{
    /**
     * Nombre de résultat par page
     * @var integer
     */
    const nbr_max = 20;

    /**
     * Nombre de résultat par page dans les fiches des éléments
     * @var integer
     */
    const nbr_max_ajax = 5;

    /**
     * Rôles des personnages dans les saisons ou les séries
     * @var array
     */
    const PERSONNAGE_SERIE_ROLES = array(
        0 => 'Invité',
        1 => 'Principal',
        2 => 'Récurrent'
    );
    
    /**
     * Acteur principal ou non d'un personnage
     * @var array
     */
    const ACTEUR_PERSONNAGE_ROLES = array(
        0 => 'Secondaire',
        1 => 'Principal'
    );
    
    /**
     * Rôles des personnages dans les saisons ou les séries
     * @var array
     */
    const CORRECTION = array(
        1 => 'Correction après chaque question',
        2 => 'Correction à la fin du quizz'
    );
    
    /**
     * Renvoie le nombre d'éléments à afficher pour un utilisateur
     * @return string
     */
    public function getNbrMax() {
        return (!is_null($this->get('session')->get('nbr_max')) ? $this->get('session')->get('nbr_max') : self::nbr_max);
    }
    
    /**
     * Renvoie le nombre de sous-élément d'un élément à afficher pour un utilisateur
     * @return string
     */
    public function getNbrMaxAjax() {
        return (!is_null($this->get('session')->get('nbr_max_ajax')) ? $this->get('session')->get('nbr_max_ajax') : self::nbr_max_ajax);
    }
    
    /**
     * 
     * @param string $type
     * @return number
     */
    public function getVo(string $type){
        return (!is_null($this->get('session')->get('user')) ? $this->get('session')->get('user')[$type . '_vo'] : 0);
    }
    
    /**
     *
     * @return string
     */
    public function homeURL()
    {
        return $this->generateUrl('index');
    }
    
    /**
     * Affichage d'un array sous une meilleure forme
     *
     * @param array $array
     */
    public function pre(array $array)
    {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }
    
    /**
     * Récupération des infos de l'utilisateur connecté
     *
     * @return Utilisateur
     */
    public function getUtilisateur()
    {
        $repo = $this->getDoctrine()->getRepository(Utilisateur::class);
        $membres = $repo->findById($this->getUser()
            ->getId());
        
        return (count($membres) ? $membres[0] : new Utilisateur());
    }
    
    /**
     *
     * @param array $elements
     * @param string $appelation
     * @return array[]
     */
    public function sortArrayCollection($elements = array(), string $appelation = "getNom", string $classe = ""){
        $names = array();
        $sort_array = array();
        
        if($classe == "PersonnageSerie" || $classe == "PersonnageSaison"){
            $repo = $this->getDoctrine()->getRepository(Personnage::class);
            foreach($elements as $element){
                $names[$element->getPersonnage()->getId()] = $element->getPersonnage()->{$appelation}();
            }
            asort($names);
            
            foreach ($names as $id => $name){
                $name;
                $sort_array[] = $repo->findOneBy(array(
                    'id' => $id
                ));
            }
        } else {
            foreach ($elements as $id => $element){
                $names[$id] = $element->{$appelation}();
            }
            asort($names);
            
            foreach ($names as $id => $name){
                $sort_array[] = $elements[$id];
            }
        }
        return $sort_array;
    }
    
    public function array_first(array $array){
        foreach(array_flip($array) as $key){
            return $key;
        }
        return null;
    }
    
    /**
     * Renvoie le type d'objet, l'ID et le nom/titre s'il existe
     * @param $objet
     * @param string $type
     * @return string
     */
    public function getIdNom($objet, string $type){
        switch($type){
            case 'acteur':
                if($objet->getSexe()){
                    return " l'actrice " . $objet->getId() . ' - ' . $objet->getPrenom() . ' ' . $objet->getNom();
                } else {
                    return " l'acteur " . $objet->getId() . ' - ' . $objet->getPrenom() . ' ' . $objet->getNom();
                }
                break;
            case 'chanson':
                return ' la chanson ' . $objet->getId() . ' - ' . $objet->getTitre();
                break;
            case 'citation':
                return ' la citation ' . $objet->getId();
                break;
            case 'episode':
                if($this->get('session')->get('user')['episode_vo']){
                    return " l'épisode " . $objet->getId() . " - " . $objet->getTitreOriginal();
                } else {
                    return " l'épisode " . $objet->getId() . " - " . $objet->getTitre();
                }
                break;
            case 'espece':
                return " l'espèce " . $objet->getId() . " - " . $objet->getNom();
                break;
            case 'nationalite':
                return ' la nationnalité ' . $objet->getId() . ' - ' . $objet->getNomFeminin();
                break;
            case 'personnage':
                return ' personnage ' . $objet->getId() . ' - ' . $objet->getNomComplet();
                break;
            case 'quizz':
                return ' quizz ' . $objet->getId() . ' - ' . $objet->getNom();
                break;
            case 'saison':
                return ' la saison ' . $objet->getId() . ' - Saison ' . $objet->getNumeroSaison();
                break;
            case 'serie':
                if(!$this->get('session')->get('user')['serie_vo'] && !is_null($objet->getTitre())){
                    return " la série " . $objet->getId() . " - " . $objet->getTitre();
                } else {
                    return " la série " . $objet->getId() . " - " . $objet->getTitreOriginal();
                }
                break;
            case 'utilisateur':
                return ' l\'utilisateur ' . $objet->getId() . ' - ' . $objet->getNomComplet();
                break;
            case 'photo':
                return ' la photo ' . $objet->getId() . ' - ' . $objet->getNom();
                break;
            case 'way_to_die':
                return ' la façon de mourir ' . $objet->getId() . ' - ' . $objet->getNom();
                break;
        }
    }
    
    /**
     * Obtention de la liste des saisons rangé par série
     * @return array[]|string
     */
    public function getSaisons(){
        $saisons = array();
        
        $query  = "";
        $query .= "SELECT Se.nom as serie_nom, Sa.id as saison_id, Sa.numero_saison as num_saison ";
        $query .= "FROM `saison` Sa, `serie` Se WHERE Se.id = Sa.serie_id ";
        $query .= "ORDER BY Se.id, Sa.numero_saison";
        
        $em = $this->getDoctrine()->getManager();
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        
        $serie_saisons = $statement->fetchAll();
        foreach($serie_saisons as $serie_saison){
            if(!isset($serie_saison["serie_nom"])){
                $saisons[$serie_saison["serie_nom"]] = array();
            }
            $saisons[$serie_saison["serie_nom"]][$serie_saison["saison_id"]] = 'Saison ' . $serie_saison["num_saison"];
        }
        return $saisons;
    }
    
    /**
     * Créer un slug unique
     * 
     * @param string $texte
     * @param string $type
     * @return string
     */
    public function createSlug(string $texte, string $type){
        $slug = htmlentities($texte, ENT_NOQUOTES, "utf-8" );
        
        $slug = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $slug);
        $slug = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $slug);
        $slug = preg_replace('#&[^;]+;#', '', $slug);
        
        $slug = str_replace(array("/", "\\", "'"), '-', $slug);
        $slug = str_replace(array("?", ",", "(", ")", ":", "[", "]", '"'), '', $slug);
        $slug = trim($slug);
        $slug = implode("_", explode(' ', $slug));
        
        switch($type){
            case 'Acteur':
                $repository = $this->getDoctrine()->getRepository(Acteur::class);
                break;
            case 'Chanson':
                $repository = $this->getDoctrine()->getRepository(Chanson::class);
                break;
            case 'Episode':
                $repository = $this->getDoctrine()->getRepository(Episode::class);
                break;
            case 'Espece':
                $repository = $this->getDoctrine()->getRepository(Espece::class);
                break;
            case 'Personnage':
                $repository = $this->getDoctrine()->getRepository(Personnage::class);
                break;
            case 'Quizz':
                $repository = $this->getDoctrine()->getRepository(Quizz::class);
                break;
            case 'Serie':
                $repository = $this->getDoctrine()->getRepository(Serie::class);
                break;
            default:
                return $texte;
                break;
        }
        
        $object = $repository->findOneBy(array('slug' => $slug));
        if(is_null($object)){
            return $slug;
        } else {
            for($i = 1; $i <= 1000; $i++){
                $object = $repository->findOneBy(array('slug' => $slug . "_" . $i));
                if(is_null($object)){
                    return $slug . "_" . $i;
                }
            }
        }
        return $slug . "_0";
    }
}
