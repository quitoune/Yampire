<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Serie;
use App\Entity\Chanson;
use App\Entity\Episode;
use App\Entity\Saison;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Form\ChansonType;

class ChansonController extends AppController
{
    /**
     * Liste des chansons
     *
     * @Route("/{slug}/chanson/liste/{page}", name="chanson_liste")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(Serie $serie, int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Chanson::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'Chanson',
            'field' => 'episode.numero_production',
            'order' => 'ASC',
            'condition' => 'serie.id = ' . $serie->getId(),
            'jointure' => array(
                array(
                    'oldrepository' => 'Chanson',
                    'newrepository' => 'episode'
                ),
                array(
                    'oldrepository' => 'episode',
                    'newrepository' => 'serie'
                )
            )
        ));
        $chansons = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'chanson_liste',
            'route_params' => array(
                'slug' => $serie->getSlug()
            )
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Chansons de ' . $serie->getNom($this->getVo("serie"))
        );

        return $this->render('chanson/index.html.twig', array(
            'chansons' => $chansons,
            'serie' => $serie,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }

    /**
     * Affichage des chansons
     *
     * @Route("/{slug}/chanson/afficher/{slug_song}/{page}", name="chanson_afficher")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("chanson", options={"mapping"={"slug_song"="slug"}})
     *
     * @param Chanson $chanson
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Serie $serie, Chanson $chanson, int $page = 1)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('chanson_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Chansons de ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => 'Affichage de' . $this->getIdNom($chanson, 'chanson')
        );

        return $this->render('chanson/afficher.html.twig', array(
            'path_modifier' => $this->generateUrl('chanson_modifier', array(
                'slug_song' => $chanson->getSlug(),
                'page' => $page,
                'slug' => $serie->getSlug()
            )),
            'path_episode' => $this->generateUrl('episode_afficher', array(
                'slug_episode' => $chanson->getEpisode()->getSlug(),
                'slug' => $serie->getSlug()
            )),
            'page' => $page,
            'chanson' => $chanson,
            'paths' => $paths
        ));
    }

    /**
     * Affichage des chansons d'une série / d'une saison / d'un episode
     *
     * @Route("/chanson/ajax/afficher/{type}/{id}/{page}", name="ajax_afficher_chansons")
     *
     * @param int $id
     * @param string $type
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficher(int $id, string $type, int $page = 1)
    {
        switch ($type) {
            case 'episode':
                $repository = $this->getDoctrine()->getRepository(Episode::class);
                break;
            case 'saison':
                $repository = $this->getDoctrine()->getRepository(Saison::class);
                break;
            case 'serie':
                $repository = $this->getDoctrine()->getRepository(Serie::class);
                break;
        }
        $objets = $repository->findById($id);
        $objet = $objets[0];

        $repo = $this->getDoctrine()->getRepository(Chanson::class);

        $params = array(
            'repository' => 'Chanson',
            'order' => 'ASC',
            'page' => $page
        );

        switch ($type) {
            case 'episode':
                $params['field'] = 'Chanson.titre';
                $params['condition'] = 'Chanson.episode = ' . $id;
                break;
            case 'saison':
                $params['field'] = 'episode.numero_episode ASC, Chanson.titre';
                $params['condition'] = 'episode.saison = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'Chanson',
                        'newrepository' => 'episode'
                    )
                );
                break;
            case 'serie':
                $params['field'] = 'episode.numero_production ASC, Chanson.titre';
                $params['condition'] = 'episode.serie = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'Chanson',
                        'newrepository' => 'episode'
                    )
                );
                break;
        }
        
        $nbr_max_ajax = $this->getNbrMaxAjax();
        $result = $repo->findAllElements($page, $nbr_max_ajax, $params);

        $pagination = array(
            'page' => $page,
            'route' => 'ajax_afficher_chansons',
            'pages_count' => ceil($result['nombre'] / $nbr_max_ajax),
            'nb_elements' => $result['nombre'],
            'route_params' => array(
                'id' => $id,
                'type' => $type
            )
        );
        
        return $this->render('chanson/ajax_afficher.html.twig', array(
            'chansons' => $result['paginator'],
            'type' => $type,
            'objet' => $objet,
            'pagination' => $pagination
        ));
    }

    /**
     * Formulaire de modifcation d'une chanson
     *
     * @Route("/{slug}/chanson/modifier/{slug_song}/{page}", name="chanson_modifier")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("chanson", options={"mapping"={"slug_song"="slug"}})
     * @IsGranted("ROLE_UTILISATEUR")
     * 
     * @param Request $request
     * @param SessionInterface $session
     * @param Chanson $chanson
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, SessionInterface $session, Serie $serie, Chanson $chanson, int $page = 1)
    {
        $form = $this->createForm(ChansonType::class, $chanson, array(
            'update' => true,
            'session' => $session,
            'choices_episodes' => $chanson->getEpisode()->getSerie()->getEpisodes()
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chanson = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($chanson);
            $manager->flush();

            return $this->redirectToRoute('chanson_afficher', array(
                'slug_song' => $chanson->getSlug(),
                'page' => $page,
                'slug' => $serie->getSlug()
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('chanson_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Chansons de ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => 'Modification de' . $this->getIdNom($chanson, 'chanson')
        );

        return $this->render('chanson/modifier.html.twig', array(
            'form' => $form->createView(),
            'chanson' => $chanson,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire de modifcation d'une chanson
     *
     * @Route("/{slug}/chanson/ajouter/{page}", name="chanson_ajouter")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @IsGranted("ROLE_UTILISATEUR")
     * 
     * @param Request $request
     * @param SessionInterface $session
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, SessionInterface $session, Serie $serie,  int $page = 1)
    {
        $chanson = new Chanson();

        $form = $this->createForm(ChansonType::class, $chanson, array(
            'session' => $session,
            'choices_episodes' => $serie->getEpisodes()
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $chanson = $form->getData();
            
            $slug = $this->createSlug($request->request->all()["chanson"]["titre"], 'Chanson');
            $chanson->setSlug($slug);
            
            $chanson->setDateCreation(new \DateTime());
            $chanson->setUtilisateur($this->getUtilisateur());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($chanson);
            $manager->flush();

            return $this->redirectToRoute('chanson_afficher', array(
                'page' => $page,
                'slug' => $serie->getSlug(),
                'slug_song' => $slug
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('chanson_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Chansons de ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => "Ajout d'une chanson"
        );

        return $this->render('chanson/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
    
    /**
     * Formulaire d'ajout d'une chanson
     *
     * @Route("/chanson/ajax_ajouter/{type}/{id}", name="chanson_ajax_ajouter")
     * @IsGranted("ROLE_UTILISATEUR")
     * 
     * @param Request $request
     * @param SessionInterface $session
     * @param string $type
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAjouter(Request $request, SessionInterface $session, string $type, int $id){
        $chanson = new Chanson();
        
        $disabled_episode = false;
        $choices_episodes = array();
        
        switch($type){
            case 'serie':
                $repo = $this->getDoctrine()->getRepository(Serie::class);
                $serie = $repo->findOneBy(array('id' => $id));
                $objet = $serie;
                
                $choices_episodes = $serie->getEpisodes();
                break;
            case 'saison':
                $repo = $this->getDoctrine()->getRepository(Saison::class);
                $saison = $repo->findOneBy(array('id' => $id));
                $serie = $saison->getSerie();
                $objet = $saison;
                
                $choices_episodes = $saison->getEpisodes();
                break;
            case 'episode':
                $disabled_episode = true;
                $repo = $this->getDoctrine()->getRepository(Episode::class);
                $episode = $repo->findOneBy(array('id' => $id));
                $serie = $episode->getSerie();
                $objet = $episode;
                
                $chanson->setEpisode($episode);
                $choices_episodes = $episode->getSaison()->getEpisodes();
                break;
        }
        
        $form = $this->createForm(ChansonType::class, $chanson, array(
            'session' => $session,
            'disabled_episode' =>$disabled_episode,
            'choices_episodes' => $choices_episodes
        ));
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $chanson = $form->getData();
            
            $slug = $this->createSlug($request->request->all()["chanson"]["titre"], 'Chanson');
            $chanson->setSlug($slug);
            
            $chanson->setDateCreation(new \DateTime());
            $chanson->setUtilisateur($this->getUtilisateur());
            
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($chanson);
            $manager->flush();
            
            return $this->json(array(
                'statut' => true
            ));
        }
        
        
        
        return $this->render('chanson/ajax_ajouter.html.twig', array(
            'form' => $form->createView(),
            'serie' => $serie,
            'type' => $type,
            'objet' => $objet
        ));
    }
}
