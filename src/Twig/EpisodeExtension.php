<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\Episode;

class EpisodeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return array(
            new TwigFilter('titre', array(
                $this,
                'getTitreEpisode'
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
                'getTitreEpisode'
            ))
        );
    }
    
    /**
     *
     * @param Episode $episode
     * @return string
     */
    public function getTitreEpisode(Episode $episode, $session){
        $is_vo = 0;
        if($session->get('user')){
            $is_vo = $session->get('user')['vo'];
        }
        
        if($is_vo){
            return $episode->getTitreOriginal();
        } elseif(!$is_vo && is_null($episode->getTitre())){
            return $episode->getTitreOriginal();
        } else {
            return $episode->getTitre();
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
            $code .= $episode->getSerie()->getNomCourt() . ' - ';
        }
        $code .= 'S' . $episode->getSaison()->getNumeroSaison() . 'E' . $episode->getNumeroEpisode();
        return $code;
    }
}
