<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Serie;
use App\Entity\Saison;
use Symfony\Component\HttpFoundation\Request;
use App\Form\SaisonType;
use App\Entity\Tag;

class SaisonController extends AppController
{
    /**
     * Liste des saisons
     *
     * @Route("/{slug}/saison/liste/{page}", name="saison_liste")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(Serie $serie, int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Saison::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'Saison',
            'field' => 'Saison.id',
            'condition' => 'serie.id = ' . $serie->getId(),
            'order' => 'ASC',
            'jointure' => array(
                array(
                    'oldrepository' => 'Saison',
                    'newrepository' => 'serie'
                )
            )
        ));
        $saisons = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'saison_liste',
            'route_params' => array(
                'slug' => $serie->getSlug()
            )
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Saisons ' . $serie->getNom($this->getVo("serie"))
        );

        return $this->render('saison/index.html.twig', array(
            'serie' => $serie,
            'saisons' => $saisons,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }

    /**
     * Affichage d'une saison
     *
     * @Route("/saison/ajax/afficher_serie/{id}", name="ajax_afficher_saison_fiche")
     * @ParamConverter("saison", options={"mapping"={"id"="id"}})
     *
     * @param Saison $saison
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficherFiche(Saison $saison)
    {
        return $this->render('saison/ajax_afficher_fiche.html.twig', array(
            'saison' => $saison
        ));
    }

    /**
     * Affichage des saisons d'une série
     *
     * @Route("/saison/ajax/afficher/{slug}/{page}", name="ajax_afficher_saisons")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     *
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficher(Serie $serie, int $page = 1)
    {
        $repo = $this->getDoctrine()->getRepository(Saison::class);

        $params = array(
            'repository' => 'Saison',
            'field' => 'Saison.numero_saison',
            'condition' => 'Saison.serie = ' . $serie->getId(),
            'order' => 'ASC',
            'page' => $page
        );

        $nbr_max_ajax = $this->getNbrMaxAjax();
        $result = $repo->findAllElements($page, $nbr_max_ajax, $params);

        $pagination = array(
            'page' => $page,
            'route' => 'ajax_afficher_saisons',
            'pages_count' => ceil($result['nombre'] / $nbr_max_ajax),
            'nb_elements' => $result['nombre'],
            'route_params' => array(
                'slug' => $serie->getSlug()
            )
        );

        return $this->render('saison/ajax_afficher.html.twig', array(
            'saisons' => $result['paginator'],
            'serie' => $serie,
            'type' => 'serie',
            'pagination' => $pagination
        ));
    }

    /**
     * Affichage d'une saison
     *
     * @Route("/{slug}/saison/afficher/{id}", name="saison_afficher")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     *
     * @param Saison $saison
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Serie $serie, Saison $saison)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('serie_afficher', array(
                    'slug' => $serie->getSlug()
                )) => $serie->getNom($this->getVo("serie"))
            ),
            'active' => 'Affichage de' . $this->getIdNom($saison, 'saison')
        );

        return $this->render('saison/afficher.html.twig', array(
            'saison' => $saison,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire de modification d'une saison
     *
     * @Route("/{slug}/saison/modifier/{id}/{page}", name="saison_modifier")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param Saison $saison
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, Serie $serie, Saison $saison, int $page = 1)
    {
        $form = $this->createForm(SaisonType::class, $saison, array(
            'update' => true
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $saison = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($saison);
            $entityManager->flush();

            return $this->redirectToRoute('saison_liste', array(
                'page' => $page,
                'slug' => $serie->getSlug()
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('saison_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Saisons ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => 'Modification' . $this->getIdNom($saison, 'saison')
        );

        return $this->render('saison/modifier.html.twig', array(
            'form' => $form->createView(),
            'saison' => $saison,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire d'ajout d'une saison
     *
     * @Route("/{slug}/saison/ajouter/{page}", name="saison_ajouter")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, Serie $serie, int $page = 1)
    {
        $saison = new Saison();

        $form = $this->createForm(SaisonType::class, $saison);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $saison = $form->getData();
            
            $tag = new Tag();
            $tag->setNom($saison->getSerie()->getTitreCourt() . ' - Saison ' . $saison->getNumeroSaison());
            $saison->setTag($tag);
            
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($tag);
            $manager->persist($saison);
            $manager->flush();

            return $this->redirectToRoute('saison_liste', array(
                'page' => $page,
                'slug' => $serie->getSlug()
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('saison_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Saisons ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => "Ajout d'une saison"
        );

        return $this->render('saison/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Affichage des épisodes d'une saison
     *
     * @Route("/saison/afficher_episodes/{id}", name="saison_afficher_episodes")
     * @ParamConverter("saison", options={"mapping"={"id"="id"}})
     *
     * @param Saison $saison
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxSeeEpisodes(Saison $saison)
    {
        return $this->render('episode/ajax_afficher_saison.html.twig', array(
            'saison' => $saison,
            'episodes' => $saison->getEpisodes()
        ));
    }
    
    /**
     * Formulaire d'ajout d'une saison dans une pop-up
     *
     * @Route("/saison/ajax/ajouter/{slug}", name="saison_ajax_ajouter")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @IsGranted("ROLE_UTILISATEUR")
     * 
     * @param Request $request
     * @param Serie $serie
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAjouter(Request $request, Serie $serie){
        $saison = new Saison();
        $saison->setSerie($serie);
        
        $form = $this->createForm(SaisonType::class, $saison, array(
            'disabled_serie' => true
        ));
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $saison = $form->getData();
            
            $tag = new Tag();
            $tag->setNom($saison->getSerie()->getTitreCourt() . ' - Saison ' . $saison->getNumeroSaison());
            $saison->setTag($tag);
            
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($saison);
            $manager->persist($tag);
            $manager->flush();
            
            return $this->json(array(
                'statut' => true
            ));
        }
        
        return $this->render('saison/ajax_ajouter.html.twig', array(
            'form' => $form->createView(),
            'serie' => $serie
        ));
    }
}
