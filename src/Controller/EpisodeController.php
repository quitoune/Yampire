<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Serie;
use App\Entity\Episode;
use App\Entity\Saison;
use App\Entity\Personnage;
use Symfony\Component\HttpFoundation\Request;
use App\Form\EpisodeType;

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
            'active' => 'Episodes de ' . $serie->getNom()
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
                if($epi->getPersonnages()->contains($personnage)){
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
                )) => 'Episodes de ' . $episode->getSerie()->getNom()
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
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
            $episode = $form->getData();

            $manager = $this->getDoctrine()->getManager();
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
                )) => 'Episodes de ' . $episode->getSerie()->getNom()
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
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

            $manager = $this->getDoctrine()->getManager();
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
                )) => 'Episodes de ' . $serie->getNom()
            ),
            'active' => "Ajout d'un épisode pour la série " . $serie->getNom()
        );

        return $this->render('episode/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
}
