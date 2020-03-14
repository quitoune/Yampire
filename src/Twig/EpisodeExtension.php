<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\Episode;
use App\Entity\Serie;
use Symfony\Component\HttpFoundation\Session\Session;

class EpisodeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return array(
            new TwigFilter('titre', array(
                $this,
                'getTitre'
            ))
        );
    }
    
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('codeEpisode', array(
                $this,
                'getCodeEpisode'
            )),
            new TwigFunction('titre', array(
                $this,
                'getTitre'
            )),
            new TwigFunction('titreMenu', array(
                $this,
                'getTitreMenu'
            ))
        );
    }
    
    /**
     *
     * @param Serie $serie
     * @param Session $session
     * @return string
     */
    public function getTitreMenu($serie, $session){
        $is_vo = 0;
        if($session->get('user')){
            $is_vo = $session->get('user')['serie_vo'];
        }
        
        if($is_vo){
            return $serie['titre_original'];
        } elseif(!$is_vo && is_null($serie['titre'])){
            return $serie['titre_original'];
        } else {
            return $serie['titre'];
        }
    }
    
    /**
     *
     * @param string $type
     * @param Serie/Episode $objet
     * @param Session $session
     * @return string
     */
    public function getTitre(string $type, $objet, $session){
        $is_vo = 0;
        if($session->get('user')){
            $is_vo = $session->get('user')[$type . '_vo'];
        }
        
        if($is_vo){
            return $objet->getTitreOriginal();
        } elseif(!$is_vo && is_null($objet->getTitre())){
            return $objet->getTitreOriginal();
        } else {
            return $objet->getTitre();
        }
    }
    
    /**
     * Donne un identifiant codé de l'épisode
     *
     * @param Episode $episode
     * @return string
     */
    public function getCodeEpisode(Episode $episode, $avec_serie = true)
    {
        $code = '';
        if($avec_serie){
            $code .= $episode->getSerie()->getTitreCourt() . ' - ';
        }
        $code .= 'S' . $episode->getSaison()->getNumeroSaison() . 'E' . $episode->getNumeroEpisode();
        return $code;
    }
}
