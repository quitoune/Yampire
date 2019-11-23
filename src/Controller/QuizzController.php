<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Quizz;
use App\Entity\QuestionQuizz;

class QuizzController extends AppController
{
    /**
     * Liste des quizzs
     * 
     * @Route("/quizz/liste/{page}", name="quizz_liste")
     * 
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Quizz::class);
        
        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'Quizz',
            'field' => 'Quizz.id',
            'order' => 'ASC'
        ));
        $quizzs = $paginator['paginator'];
        
        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'acteur_liste',
            'route_params' => array()
        );
        
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Quizzs'
        );
        
        return $this->render('quizz/index.html.twig', array(
            'controller_name' => 'QuizzController',
            'quizzs' => $quizzs,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }
    
    /**
     * Affichage d'un quizz
     *
     * @Route("/quizz/afficher/{id}/{page}", name="quizz_afficher")
     *
     * @ParamConverter("quizz", options={"mapping"={"id"="id"}})
     * 
     * @param Quizz $quizz
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Quizz $quizz, int $page = 1){
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('quizz_liste', array(
                    'page' => $page
                )) => 'Quizzs'
            ),
            'active' => 'Affichage de' . $this->getIdNom($quizz, 'quizz')
        );
        
        $question_quizz = $this->getDoctrine()
            ->getRepository(QuestionQuizz::class)
            ->findOneBy(array(
            'quizz' => $quizz,
            'ordre' => 4
        ));
        
        return $this->render('quizz/afficher.html.twig', array(
            'quizz' => $quizz,
            'page' => $page,
            'paths' => $paths,
            'question_quizz' => $question_quizz,
            'doctrine' => $this->getDoctrine()
        ));
    }
    
    /**
     * Test d'un quizz
     *
     * @Route("/quizz/lancement/{id}", name="quizz_test")
     * @ParamConverter("quizz", options={"mapping"={"id"="id"}})
     * 
     * @param Quizz $quizz
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tester(Quizz $quizz){
        $nbr = count($quizz->getQuizzQuestions());
        return $this->render('quizz/lancement.html.twig', array(
            'quizz' => $quizz,
            'nbr' => $nbr
        ));
    }
}
