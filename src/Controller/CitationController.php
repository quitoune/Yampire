<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Serie;
use App\Entity\Citation;
use App\Entity\Episode;
use App\Entity\Personnage;
use App\Entity\Saison;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Form\CitationType;

class CitationController extends AppController
{
    /**
     * Liste des citations
     *
     * @Route("/{slug}/citation/liste/{page}", name="citation_liste")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     *
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(Serie $serie, int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Citation::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'Citation',
            'field' => 'Citation.id',
            'order' => 'ASC',
            'condition' => 'serie.id = ' . $serie->getId(),
            'jointure' => array(
                array(
                    'oldrepository' => 'Citation',
                    'newrepository' => 'episode'
                ),
                array(
                    'oldrepository' => 'episode',
                    'newrepository' => 'serie'
                )
            )
        ));

        $citations = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'citation_liste',
            'route_params' => array(
                'slug' => $serie->getSlug()
            )
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Citations de ' . $serie->getNom($this->getVo("serie"))
        );

        return $this->render('citation/index.html.twig', array(
            'citations' => $citations,
            'serie' => $serie,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }

    /**
     * Affichage des notes d'un personnage / d'un acteur / d'un episode
     *
     * @Route("/{slug}/citation/ajax/afficher/{type}/{id}/{page}", name="ajax_afficher_citations")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     *
     * @param int $id
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficher(Serie $serie, int $id, string $type, int $page = 1)
    {
        switch ($type) {
            case 'episode':
                $repository = $this->getDoctrine()->getRepository(Episode::class);
                break;
            case 'personnage':
                $repository = $this->getDoctrine()->getRepository(Personnage::class);
                break;
            case 'saison':
                $repository = $this->getDoctrine()->getRepository(Saison::class);
                break;
            case 'serie':
                $repository = $this->getDoctrine()->getRepository(Serie::class);
                break;
        }
        $objet = $repository->findOneBy(array('id' => $id));

        $repo = $this->getDoctrine()->getRepository(Citation::class);

        $params = array(
            'repository' => 'Citation',
            'order' => 'ASC',
            'page' => $page
        );

        switch ($type) {
            case 'personnage':
                $params['field'] = 'serie.id ASC, episode.numero_production';
                $params['condition'] = 'Citation.from_personnage = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'Citation',
                        'newrepository' => 'episode'
                    ),
                    array(
                        'oldrepository' => 'episode',
                        'newrepository' => 'serie'
                    )
                );
                break;
            case 'episode':
                $params['field'] = 'Citation.id';
                $params['condition'] = 'Citation.episode = ' . $id;
                break;
            case 'saison':
                $params['field'] = 'episode.numero_episode ASC, Citation.id';
                $params['condition'] = 'episode.saison = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'Citation',
                        'newrepository' => 'episode'
                    )
                );
                break;
            case 'serie':
                $params['field'] = 'episode.numero_production ASC, Citation.id';
                $params['condition'] = 'episode.serie = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'Citation',
                        'newrepository' => 'episode'
                    )
                );
                break;
        }

        $nbr_max_ajax = $this->getNbrMaxAjax();
        $result = $repo->findAllElements($page, $nbr_max_ajax, $params);

        $pagination = array(
            'page' => $page,
            'route' => 'ajax_afficher_citations',
            'pages_count' => ceil($result['nombre'] / $nbr_max_ajax),
            'nb_elements' => $result['nombre'],
            'route_params' => array(
                'slug' => $serie->getSlug(),
                'id' => $id,
                'type' => $type
            )
        );

        return $this->render('citation/ajax_afficher.html.twig', array(
            'citations' => $result['paginator'],
            'serie' => $serie,
            'type' => $type,
            'objet' => $objet,
            'pagination' => $pagination
        ));
    }

    /**
     * Formulaire de modification d'une citation
     *
     * @Route("/{slug}/citation/modifier/{id}/{page}", name="citation_modifier")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @IsGranted("ROLE_UTILISATEUR")
     * 
     * @param Request $request
     * @param SessionInterface $session
     * @param Citation $citation
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, SessionInterface $session, Serie $serie, Citation $citation, int $page = 1)
    {
        $form = $this->createForm(CitationType::class, $citation, array(
            'update' => 'Sauvegarder',
            'session' => $session,
            'choices_episodes' => $citation->getEpisode()->getSerie()->getEpisodes(),
            'choices_personnages' => $this->sortArrayCollection($serie->getPersonnageSeries(), "getNomToSort", "PersonnageSerie")
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $citation = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($citation);
            $manager->flush();

            return $this->redirectToRoute('citation_afficher', array(
                'page' => $page,
                'slug' => $serie->getSlug(),
                'id' => $citation->getId()
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('citation_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Citations de ' . $citation->getEpisode()->getSerie()->getNom($this->getVo("serie"))
            ),
            'active' => 'Modification de' . $this->getIdNom($citation, 'citation')
        );

        return $this->render('citation/modifier.html.twig', array(
            'form' => $form->createView(),
            'citation' => $citation,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Affichage d'une citation
     *
     * @Route("/{slug}/citation/afficher/{id}/{page}", name="citation_afficher")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     *
     * @param Citation $citation
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Serie $serie, Citation $citation, int $page = 1)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('citation_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Citations de ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => 'Affichage de' . $this->getIdNom($citation, 'citation')
        );
        
        $path_update = $this->generateUrl('citation_modifier', array(
            'id'   => $citation->getId(),
            'page' => $page,
            'slug' => $serie->getSlug()
        ));
        
        $path_episode = $this->generateUrl('episode_afficher', array(
            'slug_episode' => $citation->getEpisode()->getSlug(),
            'slug' => $serie->getSlug()
        ));

        return $this->render('citation/afficher.html.twig', array(
            'page' => $page,
            'citation' => $citation,
            'path_update' => $path_update,
            'path_episode' => $path_episode,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire d'ajout d'une citation
     *
     * @Route("/{slug}/citation/ajouter/{page}", name="citation_ajouter")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, SessionInterface $session, Serie $serie, int $page = 1)
    {
        $citation = new Citation();

        $form = $this->createForm(CitationType::class, $citation, array(
            'session' => $session,
            'choices_episodes' => $serie->getEpisodes(),
            'choices_personnages' => $this->sortArrayCollection($serie->getPersonnageSeries(), "getNomToSort", "PersonnageSerie")
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $citation = $form->getData();

            $manager = $this->getDoctrine()->getManager();

            $citation->setDateCreation(new \DateTime());
            $citation->setUtilisateur($this->getUtilisateur());

            $manager->persist($citation);
            $manager->flush();

            return $this->redirectToRoute('citation_liste', array(
                'page' => $page,
                'slug' => $serie->getSlug()
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('citation_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Citations de ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => "Ajout d'une citation pour la série " . $serie->getNom($this->getVo("serie"))
        );

        return $this->render('citation/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
    
    /**
     * Formulaire d'ajout d'une citation
     *
     * @Route("/{slug}/citation/ajax_ajouter/{type}/{id}", name="citation_ajax_ajouter")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @IsGranted("ROLE_UTILISATEUR")
     * 
     * @param Request $request
     * @param SessionInterface $session
     * @param Serie $serie
     * @param string $type
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAjouter(Request $request, SessionInterface $session, Serie $serie, string $type, int $id){
        $citation = new Citation();
        
        $disabled_episode = false;
        $disabled_personnage = false;
        $choices_episodes = array();
        $choices_personnages = array();
        
        switch($type){
            case 'serie':
                $objet = $serie;
                $choices_episodes = $serie->getEpisodes();
                $choices_personnages = $this->sortArrayCollection($serie->getPersonnageSeries(), "getNomToSort", "PersonnageSerie");
                break;
            case 'saison':
                $repo = $this->getDoctrine()->getRepository(Saison::class);
                $saison = $repo->findOneBy(array('id' => $id));
                $objet = $saison;
                
                $choices_episodes = $saison->getEpisodes();
                $choices_personnages = $this->sortArrayCollection($saison->getPersonnageSaisons(), "getNomToSort", "PersonnageSaison");
                break;
            case 'episode':
                $disabled_episode = true;
                $repo = $this->getDoctrine()->getRepository(Episode::class);
                $episode = $repo->findOneBy(array('id' => $id));
                $objet = $episode;
                
                $citation->setEpisode($episode);
                $choices_episodes = $episode->getSaison()->getEpisodes();
                $choices_personnages = $this->sortArrayCollection($episode->getPersonnage());
                break;
            case 'personnage':
                $disabled_personnage = true;
                $repo = $this->getDoctrine()->getRepository(Personnage::class);
                $personnage = $repo->findOneBy(array('id' => $id));
                $objet = $personnage;
                
                $citation->setFromPersonnage($personnage);
                $choices_episodes = $personnage->getEpisodes();
                $choices_personnages = $this->sortArrayCollection($serie->getPersonnageSeries(), "getNomToSort", "PersonnageSerie");
                break;
        }
        
        $form = $this->createForm(CitationType::class, $citation, array(
            'session' => $session,
            'disabled_episode' => $disabled_episode,
            'disabled_personnage' => $disabled_personnage,
            'choices_episodes' => $choices_episodes,
            'choices_personnages' => $choices_personnages
        ));
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $citation = $form->getData();
            
            $manager = $this->getDoctrine()->getManager();
            
            $citation->setDateCreation(new \DateTime());
            $citation->setUtilisateur($this->getUtilisateur());
            
            $manager->persist($citation);
            $manager->flush();
            
            return $this->json(array(
                'statut' => true
            ));
        }
        
        return $this->render('citation/ajax_ajouter.html.twig', array(
            'form' => $form->createView(),
            'serie' => $serie,
            'type' => $type,
            'objet' => $objet
        ));
    }
}
