<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Serie;
use App\Entity\Episode;
use App\Entity\Saison;
use App\Entity\Personnage;
use Symfony\Component\HttpFoundation\Request;
use App\Form\EpisodeType;
use App\Form\EpisodePersonnageType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Tag;

class EpisodeController extends AppController
{
    /**
     * Liste des épisodes
     *
     * @Route("/{slug}/episode/liste/{page}", name="episode_liste")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     *
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(Serie $serie, int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Episode::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'Episode',
            'field' => 'Episode.id',
            'order' => 'ASC',
            'condition' => 'serie.id = ' . $serie->getId(),
            'jointure' => array(
                array(
                    'oldrepository' => 'Episode',
                    'newrepository' => 'serie'
                )
            )
        ));
        $episodes = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'episode_liste',
            'route_params' => array(
                'slug' => $serie->getSlug()
            )
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Episodes de ' . $serie->getNom($this->getVo("serie"))
        );

        return $this->render('episode/index.html.twig', array(
            'episodes' => $episodes,
            'serie' => $serie,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }

    /**
     * Affichage d'un épisode
     *
     * @Route("/{slug}/episode/{slug_episode}/ajax/afficher/{page}", name="ajax_afficher_episode_fiche")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("episode", options={"mapping"={"slug_episode"="slug"}})
     *
     * @param Episode $episode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficherFiche(Serie $serie, Episode $episode, int $page)
    {
        return $this->render('episode/ajax_afficher_fiche.html.twig', array(
            'episode' => $episode,
            'page' => $page
        ));
    }
    
    /**
     * Affichage des épisodes d'une saison
     *
     * @Route("/episode/ajax/afficher/saison/{id}/{type}/{perso_id}", name="ajax_afficher_episodes")
     * @ParamConverter("saison", options={"mapping"={"id"="id"}})
     * @ParamConverter("personnage", options={"mapping"={"perso_id"="id"}})
     *
     * @param int $id
     * @param string $type
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficher(Saison $saison, string $type, Personnage $personnage = null)
    {
        $episodes = array();
        $epis = $saison->getEpisodes();
        if(is_null($personnage)){
            $episodes = $epis;
        } else {
            foreach ($epis as $epi){
                if($epi->getPersonnage()->contains($personnage)){
                    $episodes[] = $epi;
                }
            }
        }
        
        return $this->render('episode/ajax_afficher.html.twig', array(
            'type' => $type,
            'episodes' => $episodes
        ));
    }

    /**
     * Affichage d'un épisode
     *
     * @Route("/{slug}/episode/{slug_episode}/afficher/{page}", name="episode_afficher")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("episode", options={"mapping"={"slug_episode"="slug"}})
     *
     * @param Episode $episode
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Serie $serie, Episode $episode, int $page = 1)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('episode_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Episodes de ' . $episode->getSerie()->getNom($this->getVo("serie"))
            ),
            'active' => 'Affichage de' . $this->getIdNom($episode, 'episode')
        );

        return $this->render('episode/afficher.html.twig', array(
            'episode' => $episode,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire de modification d'un épisode
     *
     * @Route("/{slug}/episode/{slug_episode}/modifier/{page}", name="episode_modifier")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("episode", options={"mapping"={"slug_episode"="slug"}})
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param Episode $episode
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, Serie $serie, Episode $episode, int $page = 1)
    {
        $form = $this->createForm(EpisodeType::class, $episode, array(
            'choices_saison' => $episode->getSerie()->getSaisons(),
            'update' => true
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($episode);
            $manager->flush();

            return $this->redirectToRoute('episode_afficher', array(
                'page' => $page,
                'slug_episode' => $episode->getSlug(),
                'slug' => $serie->getSlug()
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('episode_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Episodes de ' . $episode->getSerie()->getNom($this->getVo("serie"))
            ),
            'active' => 'Modification de' . $this->getIdNom($episode, 'episode')
        );

        return $this->render('episode/modifier.html.twig', array(
            'form' => $form->createView(),
            'episode' => $episode,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire d'ajout d'un épisode
     *
     * @Route("/{slug}/episode/ajouter/{page}", name="episode_ajouter")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, Serie $serie, int $page = 1)
    {
        $episode = new Episode();

        $form = $this->createForm(EpisodeType::class, $episode, array(
            'choices_saison' => $serie->getSaisons()
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $episode = $form->getData();
            $episode->setSerie($serie);
            $slug = $this->createSlug($request->request->all()["episode"]["titre_original"], 'Episode');
            
            $tag = new Tag();
            $tag_name = $episode->getSerie()->getTitreCourt() . ' - ';
            $tag_name .= 'S' . $episode->getSaison()->getNumeroSaison();
            $tag_name .= 'E' . ($episode->getNumeroEpisode() < 10 ? '0' . $episode->getNumeroEpisode() : $episode->getNumeroEpisode());
            $tag->setNom($tag_name);
            
            $episode->setSlug($slug);
            $episode->setTag($tag);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($tag);
            $manager->persist($episode);
            $manager->flush();

            return $this->redirectToRoute('episode_liste', array(
                'page' => $page,
                'slug' => $serie->getSlug()
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('episode_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Episodes de ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => "Ajout d'un épisode pour la série " . $serie->getNom($this->getVo("serie"))
        );

        return $this->render('episode/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
    
    /**
     * Ajouter une connexion entre un personnage et un épisode
     *
     * @Route("/personnage_episode/ajax_ajouter/{id}", name="ajax_ajouter_personnage_episode")
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param Episode $episode
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAjouterPersonnages(Request $request, SessionInterface $session, Episode $episode){
        $form = $this->createForm(EpisodePersonnageType::class, null, array(
            'session' => $session,
            'disabled_episode' => true
        ));
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if(isset($request->request->all()["episode_personnage"]["personnage"])){
                $repo = $this->getDoctrine()->getRepository(Personnage::class);
                $personnages = $request->request->all()["episode_personnage"]["personnage"];
                
                foreach($personnages as $personnage){
                    $episode->addPersonnage($repo->findOneBy(array('id' => $personnage)));
                }
                $manager = $this->getDoctrine()->getManager();
                
                $manager->persist($episode);
                $manager->flush();
            }
            
            return $this->json(array(
                'statut' => true
            ));
        }
        
        return $this->render('personnage/ajax_ajouter_depuis_episode.html.twig', array(
            'form'  => $form->createView(),
            'episode' => $episode
        ));
    }
    
    /**
     * Supprimer une connexion entre un personnage et un épisode
     *
     * @Route("/personnage_episode/ajax_supprimer/{id}", name="ajax_supprimer_personnage_episode")
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param Episode $episode
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxSupprimerPersonnages(Request $request, SessionInterface $session, Episode $episode){
        $form = $this->createForm(EpisodePersonnageType::class, null, array(
            'session' => $session,
            'label_button' => 'Supprimer',
            'choices' => $episode->getPersonnage(),
            'disabled_episode' => true
        ));
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if(isset($request->request->all()["episode_personnage"]["personnage"])){
                $repo = $this->getDoctrine()->getRepository(Personnage::class);
                $personnages = $request->request->all()["episode_personnage"]["personnage"];
                
                foreach($personnages as $personnage){
                    $episode->removePersonnage($repo->findOneBy(array('id' => $personnage)));
                }
                $manager = $this->getDoctrine()->getManager();
                
                $manager->persist($episode);
                $manager->flush();
            }
            
            return $this->json(array(
                'statut' => true
            ));
        }
        
        return $this->render('personnage/ajax_supprimer_depuis_episode.html.twig', array(
            'form'  => $form->createView(),
            'episode' => $episode
        ));
    }
}
