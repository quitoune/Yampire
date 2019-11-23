<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\Citation;
use App\Entity\Personnage;

class CitationExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('destinataires', array(
                $this,
                'getDestinataires'
            )),
            new TwigFunction('card_citation', array(
                $this,
                'getCardCitation'
            ))
        );
    }
    
    /**
     * Donne le nom du ou des destinataires
     *
     * @param Citation $citation
     * @return $string
     */
    public function getDestinataires(Citation $citation, bool $pronom = false)
    {
        $destinataires = "";
        
        if (is_null($citation->getToPersonnage1())) {
            if($pronom){
                $destinataires = "(" . $citation->getToPersonnage() . ")";
            } else {
                $destinataires = $citation->getToPersonnage();
            }
        } else {
            $destinataires = ($pronom ? "à " : "") . $this->getNomComplet($citation->getToPersonnage1());
            if (! is_null($citation->getToPersonnage2())) {
                $destinataires .= " et " . $this->getNomComplet($citation->getToPersonnage2());
            }
        }
        return $destinataires;
    }
    
    /**
     * Renvoi le nom d'un personnage en fonction de ses informations
     *
     * @param Personnage $personnage
     * @return string
     */
    public function getNomComplet(Personnage $personnage)
    {
        if (is_null($personnage->getPrenomUsage())) {
            $nom_complet = $personnage->getPrenom();
        } else {
            $nom_complet = $personnage->getPrenomUsage();
        }
        $nom_complet .= ' ' . $personnage->getNom();
        
        return $nom_complet;
    }
    
    /**
     * Renvoi le nom d'un personnage en fonction de ses informations
     *
     * @param Personnage $personnage
     * @return string
     */
    private function getNomUsage(Personnage $personnage)
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
     *
     * @param Citation $citation
     * @param boolean $index
     * @param string $path
     * @return string
     */
    public function getCardCitation(Citation $citation, $index = false, $path = ""){
        $texte = $citation->getTexte();
        if($index && strlen($texte) > 150){
            $texte = substr($texte, 0, 150) . '...';
        }
        
        $card_citation = '';
        
        if($index){
            $card_citation .= '<a href="' . $path . '" class="citation card">';
        } else {
            $card_citation .= '<div class="citation card">';
        }
        
        $card_citation .= '<div class="card-header">';
        $card_citation .= 'Citation - ID ' . $citation->getId();
        if(!$index){
            $card_citation .= '<a href="' . $path . '" class="float-right"><i class="fas fa-pen"></i></a>';
        }
        $card_citation .= '</div>';
        
        $card_citation .= '<div class="card-body">';
        $card_citation .= '<blockquote class="blockquote text-center">';
        $card_citation .= '<p class="mb-0">' . $texte . '</p>';
        $card_citation .= '<footer class="blockquote-footer">';
        $card_citation .= '<b>' . $this->getNomUsage($citation->getFromPersonnage()) . '</b> ';
        if($index){
            $card_citation .= $citation->getEpisode()->getCodeEpisode(true);
        } else {
            $card_citation .= $this->getDestinataires($citation, true) . ' dans ';
            $card_citation .= '<cite title="Source Title">';
            $card_citation .= $citation->getEpisode()->getTitreOriginal() . ' ';
            $card_citation .= $citation->getEpisode()->getCodeEpisode(true);
            
        }
        $card_citation .= '</cite>';
        $card_citation .= '</footer>';
        $card_citation .= '</blockquote>';
        $card_citation .= '</div>';
        
        $card_citation .= '<div class="card-footer text-muted text-center">';
        if($index){
            $card_citation .= $citation->getUtilisateur()->getUsername();
        } else {
            $card_citation .= 'Créée le ' . $citation->getDateCreation()->format('d/m/Y à H:i');
            $card_citation .= ' par ' . $citation->getUtilisateur()->getNomComplet();
        }
        $card_citation .= '</div>';
        
        if($index){
            $card_citation .= '</a>';
        } else {
            $card_citation .= '</div>';
        }
        
        return $card_citation;
    }
}
