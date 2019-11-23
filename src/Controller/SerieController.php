<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Serie;
use Symfony\Component\HttpFoundation\Request;
use App\Form\SerieType;
use App\Entity\Saison;

class SerieController extends AppController
{
    /**
     * Liste des séries
     *
     * @Route("/serie/liste/{page}", name="serie_liste")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Serie::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'Serie',
            'field' => 'Serie.id',
            'order' => 'ASC'
        ));
        $series = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'serie_liste',
            'route_params' => array()
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Séries'
        );

        return $this->render('serie/index.html.twig', array(
            'series' => $series,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }
    
    /**
     * Affichage d'une série
     *
     * @Route("/serie/ajax/afficher/{id}", name="ajax_afficher_serie_fiche")
     * @ParamConverter("serie", options={"mapping"={"id"="id"}})
     *
     * @param Serie $serie
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficherFiche(Serie $serie)
    {
        return $this->render('serie/ajax_afficher_fiche.html.twig', array(
            'serie' => $serie
        ));
    }
    
    /**
     * Affichage d'une série
     *
     * @Route("/serie/ajax/modifier/{id}", name="ajax_modifier_serie_fiche")
     * @ParamConverter("serie", options={"mapping"={"id"="id"}})
     *
     * @param Serie $serie
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxModifierFiche(Request $request, Serie $serie)
    {
        $form = $this->createForm(SerieType::class, $serie, array(
            'update' => true
        ));
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $serie = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($serie);
            $entityManager->flush();
            
            return $this->redirectToRoute('ajax_afficher_serie_fiche', array(
                'id' => $serie->getId()
            ));
        }
        
        return $this->render('serie/ajax_modifier_fiche.html.twig', array(
            'form' => $form->createView(),
            'serie' => $serie
        ));
    }

    /**
     * Formulaire d'affichage d'une série
     *
     * @Route("/{slug}/afficher", name="serie_afficher")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     *
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Serie $serie)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Série ' . $serie->getNom()
        );

        return $this->render('serie/afficher.html.twig', array(
            'serie' => $serie,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire de modifcation d'une série
     *
     * @Route("/{slug}/modifier/{page}", name="serie_modifier")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
     *
     * @param Request $request
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, Serie $serie, int $page = 1)
    {
        $form = $this->createForm(SerieType::class, $serie, array(
            'update' => true
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serie = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($serie);
            $entityManager->flush();

            return $this->redirectToRoute('serie_afficher', array(
                'page' => $page,
                'slug' => $serie->getSlug()
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('serie_liste', array(
                    'page' => $page
                )) => 'Séries'
            ),
            'active' => 'Modification de' . $this->getIdNom($serie, 'serie')
        );

        return $this->render('serie/modifier.html.twig', array(
            'form' => $form->createView(),
            'serie' => $serie,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire d'ajout d'une série
     *
     * @Route("/serie/ajouter/{page}", name="serie_ajouter")
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
     *
     * @param Request $request
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, int $page = 1)
    {
        $serie = new Serie();

        $form = $this->createForm(SerieType::class, $serie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $serie = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($serie);
            $manager->flush();

            return $this->redirectToRoute('serie_liste', array(
                'page' => $page
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('serie_liste', array(
                    'page' => $page
                )) => 'Séries'
            ),
            'active' => "Ajout d'une série"
        );

        return $this->render('serie/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
    
    /**
     * Affichage des épisodes d'une série
     *
     * @Route("/serie/afficher_episodes/{id}", name="serie_afficher_episodes")
     * @ParamConverter("serie", options={"mapping"={"id"="id"}})
     *
     * @param Serie $serie
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxSeeEpisodes(Serie $serie)
    {
        $saisons = array();
        
        $seasons = $serie->getSaisons();
        foreach ($seasons as $season) {
            $saisons[$season->getId()] = "Saison " . $season->getNumeroSaison();
        }
        
        $id_saison = $this->array_first($saisons);
        
        $saison = $this->getDoctrine()->getRepository(Saison::class)->findOneBy(array('id' => $id_saison));
        
        if(!is_null($saison)){
            $episodes = $saison->getEpisodes();
        } else {
            $episodes = array();
        }
        
        return $this->render('episode/ajax_afficher_serie.html.twig', array(
            'saisons' => $saisons,
            'episodes' => $episodes
        ));
    }
}
