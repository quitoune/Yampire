<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Question;

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
    
    /**
     * Liste des séries
     *
     * @Route("/question/liste/{page}", name="question_liste")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Question::class);
        
        $questions = $repository->findAll();
        
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Questions'
        );
        
        return $this->render('question/index.html.twig', array(
            'questions' => $questions,
            'paths' => $paths
        ));
    }
}
