<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UtilisateurExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('info_password', array(
                $this,
                'getInfoPassword'
            ))
        );
    }
    
    public function getInfoPassword()
    {
        $info  = "- avoir entre <b>6</b> et <b>20</b> caractères<br>";
        $info .= "- contenir <b>au moins une lettre en majuscule</b><br>";
        $info .= "- contenir <b>au moins une lettre en minuscule</b><br>";
        $info .= "- contenir <b>au moins un chiffre</b><br>";
        
        return $info;
    }
}
