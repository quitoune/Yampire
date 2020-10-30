<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\WayToDie;
use Symfony\Component\HttpFoundation\Request;
use App\Form\WayToDieType;

class WayToDieController extends AppController
{
    /**
     * Liste des façons de mourir
     *
     * @Route("/way_to_die/liste/{page}", name="way_to_die_liste")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(WayToDie::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max);
        $way_to_dies = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'way_to_die_liste',
            'route_params' => array(
                'page' => $page
            )
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Façons de mourir'
        );

        return $this->render('way_to_die/index.html.twig', array(
            'way_to_dies' => $way_to_dies,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }
    
    /**
     * Formulaire de modification d'une façon de mourir
     *
     * @Route("/way_to_die/modifier/{id}/{page}", name="way_to_die_modifier")
     * @ParamConverter("way_to_die", options={"mapping"={"id"="id"}})
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param WayToDie $way_to_die
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, WayToDie $way_to_die, int $page)
    {
        $form = $this->createForm(WayToDieType::class, $way_to_die, array(
            'update' => true
        ));
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $way_to_die = $form->getData();
            
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($way_to_die);
            $manager->flush();
            
            return $this->redirectToRoute('way_to_die_liste', array(
                'page' => $page
            ));
        }
        
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('way_to_die_liste', array(
                    'page' => $page
                )) => 'Façon de mourir'
            ),
            'active' => 'Modification de' . $this->getIdNom($way_to_die, 'way_to_die')
        );
        
        return $this->render('way_to_die/modifier.html.twig', array(
            'form' => $form->createView(),
            'way_to_die' => $way_to_die,
            'page' => $page,
            'paths' => $paths
        ));
    }
    
    /**
     * Formulaire d'ajout d'une façon de mourir
     *
     * @Route("/way_to_die/ajouter/{page}", name="way_to_die_ajouter")
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, int $page = 1)
    {
        $way_to_die = new WayToDie();
        
        $form = $this->createForm(WayToDieType::class, $way_to_die);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $way_to_die = $form->getData();
            
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($way_to_die);
            $manager->flush();
            
            return $this->redirectToRoute('way_to_die_liste', array(
                'page' => $page
            ));
        }
        
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('way_to_die_liste', array(
                    'page' => $page
                )) => 'Façons de mourir'
            ),
            'active' => "Ajout d'une façon de mourir"
        );
        
        return $this->render('way_to_die/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
    
    /**
     * Supprimer une façon de mourir
     *
     * @Route("/way_to_die/supprimer/{id}/{page}", name="way_to_die_supprimer")
     * @ParamConverter("way_to_die", options={"mapping"={"id"="id"}})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param WayToDie $way_to_die
     * @param int $page
     */
    public function supprimer(WayToDie $way_to_die, int $page)
    {
        
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($way_to_die);
        $manager->flush();
        
        return $this->redirectToRoute('way_to_die_liste', array(
            'page' => $page
        ));
    }
}
