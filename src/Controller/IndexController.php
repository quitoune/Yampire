<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Serie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class IndexController extends AppController
{
    /**
     *
     * @Route("/", name="index")
     */
    public function index(SessionInterface $session)
    {
        if(is_null($session->get('series', null))){
            $repository = $this->getDoctrine()->getRepository(Serie::class);
            $series = $repository->findAll();
            
            $session_series = array();
            
            foreach ($series as $serie) {
                $session_series[$serie->getId()] = array(
                    'id'  => $serie->getId(),
                    'titre'  => $serie->getTitre(),
                    'titre_original'  => $serie->getTitreOriginal(),
                    'slug' => $serie->getSlug()
                );
            }
            
            $session->set('series', $session_series);
        }
        
        if(is_null($session->get('user', null))){
            $session->set('user', array(
                'episode_vo' => 0,
                'serie_vo' => 0
            ));
        } else {
            if(!isset($session->get('user')['serie_vo'])){
                $session->get('user')['serie_vo'] = 0;
            }
            if(!isset($session->get('user')['episode_vo'])){
                $session->get('user')['episode_vo'] = 0;
            }
        }
        
        return $this->render('index/index.html.twig', array(
            'paths' => array(
                'home' => $this->homeURL()
            )
        ));
    }
}
