<?php
namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Serie;

class LoginListener
{

    private $em;

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        // Get the User entity.
        $user = $event->getAuthenticationToken()->getUser();

        $this->session->set('user', array(
            'prenom' => $user->getPrenom(),
            'nom' => $user->getNom(),
            'username' => $user->getUsername(),
            'vo' => $user->getVo()
        ));
        
        if($user->getNbrMax()){
            $this->session->set('nbr_max', $user->getNbrMax());
        }
        
        if($user->getNbrMaxAjax()){
            $this->session->set('nbr_max_ajax', $user->getNbrMaxAjax());
        }
        
        if($user->getCorrection()){
            $this->session->set('correction', $user->getCorrection());
        } else {
            $this->session->set('correction', 1);
        }

        if(is_null($this->session->get('series', null))){
            $series = $this->em->getRepository(Serie::class)->findAll();
            
            $session_series = array();
            
            foreach ($series as $serie) {
                $session_series[$serie->getId()] = array(
                    'id'   => $serie->getId(),
                    'nom'  => $serie->getNom(),
                    'slug' => $serie->getSlug()
                );
            }
            
            $this->session->set('series', $session_series);
            
        }
    }
}