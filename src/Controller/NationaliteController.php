<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Nationalite;
use Symfony\Component\HttpFoundation\Request;
use App\Form\NationaliteType;

class NationaliteController extends AppController
{
    /**
     * Liste des nationalités
     *
     * @Route("/nationalite/liste/{page}", name="nationalite_liste")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Nationalite::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max);
        $nationalites = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'nationalite_liste',
            'route_params' => array()
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Nationalités'
        );

        return $this->render('nationalite/index.html.twig', array(
            'nationalites' => $nationalites,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire de modification d'une nationalité
     *
     * @Route("/nationalite/modifier/{id}/{page}", name="nationalite_modifier")
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
     *
     * @param Request $request
     * @param Nationalite $nationalite
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, Nationalite $nationalite, int $page = 1)
    {
        $form = $this->createForm(NationaliteType::class, $nationalite, array(
            'update' => true
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nationalite = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($nationalite);
            $manager->flush();

            return $this->redirectToRoute('nationalite_liste', array(
                'page' => $page
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('nationalite_liste', array(
                    'page' => $page
                )) => 'Nationalités'
            ),
            'active' => 'Modification de' . $this->getIdNom($nationalite, 'nationalite')
        );

        return $this->render('nationalite/modifier.html.twig', array(
            'form' => $form->createView(),
            'nationalite' => $nationalite,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire d'ajout d'une nationalité
     *
     * @Route("/nationalite/ajouter/{page}", name="nationalite_ajouter")
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, int $page = 1)
    {
        $nationalite = new Nationalite();

        $form = $this->createForm(NationaliteType::class, $nationalite);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nationalite = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($nationalite);
            $manager->flush();

            return $this->redirectToRoute('nationalite_liste', array(
                'page' => $page
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('nationalite_liste', array(
                    'page' => $page
                )) => 'Nationalités'
            ),
            'active' => "Ajout d'une nationalité"
        );

        return $this->render('nationalite/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
    
    /**
     * Confirmer la suppression d'une nationalité
     *
     * @Route("/nationalite/confirmer/{id}/{page}", name="nationalite_confirmer")
     * @ParamConverter("nationalite", options={"mapping"={"id"="id"}})
     * 
     * @param Nationalite $nationalite
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmerSuppression(Nationalite $nationalite, int $page){
        return $this->render('nationalite/supprimer.html.twig', array(
            'nationalite' => $nationalite,
            'page' => $page
        ));
    }
    
    /**
     * Supprimer une nationalité
     *
     * @Route("/nationalite/supprimer/{id}", name="nationalite_supprimer")
     * @ParamConverter("nationalite", options={"mapping"={"id"="id"}})
     *
     * @param Nationalite $nationalite
     */
    public function supprimer(Nationalite $nationalite)
    {
        
        $manager = $this->getDoctrine()->getManager();
        
        $acteurs = $nationalite->getActeurs();
        
        foreach ($acteurs as $acteur){
            $nationalite->removeActeur($acteur);
        }
        
        $manager->persist($nationalite);
        
        $manager->remove($nationalite);
        $manager->flush();
        
        
        return $this->json(array(
            'statut' => true
        ));
    }
}
