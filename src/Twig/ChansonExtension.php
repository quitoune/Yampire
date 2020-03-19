<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\Chanson;

class ChansonExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('card_chanson', array(
                $this,
                'getCardChanson'
            ))
        );
    }
    
    /**
     *
     * @param Chanson $chanson
     * @param boolean $index
     * @param string $path
     * @return string
     */
    public function getCardChanson(Chanson $chanson, $index = false, $path = "", $session = null)
    {
        $card_chanson  = '';
        
        if($index){
            $card_chanson .= '<a href="' . $path . '" class="chanson card border-info mb-3">';
        } else {
            $card_chanson .= '<div class="chanson card border-info mb-3">';
        }
        
        $card_chanson .= '<div class="card-body">';
        $card_chanson .= '<div class="row">';
        $card_chanson .= '<div class="col-2 symbol">';
        $card_chanson .= '<i class="fas fa-music"></i>';
        $card_chanson .= '</div>';
        $card_chanson .= '<div class="col-10">';
        $card_chanson .= '<span class="music">' . $chanson->getTitre() . '</span><br>';
        $card_chanson .= '<span class="interprete">' . $chanson->getInterprete() . '</span>';
        $card_chanson .= '</div>';
        $card_chanson .= '</div>';
        if(!$index){
            $card_chanson .= '<div class="mute">' . $chanson->getEvenement() . '</div>';
        }
        
        $card_chanson .= '<div class="episode">';
        if(!$index){
            $card_chanson .= '<a href="' . $path . '">';
        }
        if(!is_null($session) && $session->get('user') != null){
            $card_chanson .= $chanson->getEpisode()->getNom($session->get('user')['episode_vo']);
        } else {
            $card_chanson .= $chanson->getEpisode()->getTitreOriginal();
        }
        $card_chanson .= ' (' . $chanson->getEpisode()->getCodeEpisode(false) . ')</div>';
        if(!$index){
            $card_chanson .= '</a>';
        }
        $card_chanson .= '</div>';
        
        if($index){
            $card_chanson .= '</a>';
        } else {
            $card_chanson .= '</div>';
        }
        return $card_chanson;
    }
}
