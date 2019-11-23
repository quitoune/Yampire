<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AppController
{
    /**
     * Type de proposition possible
     * 
     * @var array
     */
    const TYPE_PROPOSITION = array(
        1  => 'Personnage',
        2  => 'Façon de mourir',
        3  => 'Texte',
        4  => 'Chanson',
        5  => 'Episode',
        6  => 'Espèce',
        7  => 'Acteur',
        8  => 'Citation',
        9  => 'Nationalite',
        10 => 'Date'
    );
    
    /**
     * Type de question possible
     *
     * @var array
     */
    const TYPE_QUESTION = array(
        0 => 'Question',
        1 => 'Bloodline',
        2 => 'Citation',
        3 => 'Death',
        4 => 'Intrus',
        5 => 'Killer',
        6 => 'Kisser',
        7 => 'Vrai_Faux'
    );
    
    const REPONSE = array(
        1 => 'Proposition 1',
        2 => 'Proposition 2',
        3 => 'Proposition 3',
        4 => 'Proposition 4',
        5 => 'Proposition 5'
    );
}
