<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Quizz;
use App\Entity\QuestionQuizz;

class QuestionQuizzController extends AppController
{
    /**
     * Affichage d'un quizz
     *
     * @Route("/question_quizz/afficher/{id}/{ordre}/{nbr}", name="question_quizz_afficher")
     *
     * @ParamConverter("quizz", options={"mapping"={"id"="id"}})
     *
     * @param Quizz $quizz
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Quizz $quizz, int $ordre, int $nbr)
    {
        $question_quizz = $this->getDoctrine()
        ->getRepository(QuestionQuizz::class)
        ->findOneBy(array(
            'quizz' => $quizz,
            'ordre' => $ordre
        ));
        
        return $this->render('question_quizz/afficher.html.twig', array(
            'quizz' => $quizz,
            'question_quizz' => $question_quizz,
            'doctrine' => $this->getDoctrine(),
            'nbr' => $nbr,
            'ordre' => $ordre
        ));
    }
}
