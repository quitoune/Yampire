<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Route("/citation/ajax/afficher/{type}/{id}/{page}", name="ajax_afficher_citations")
     *
     * @param int $id
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficher(int $id, string $type, int $page = 1)
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
        $objets = $repository->findById($id);
        $objet = $objets[0];

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
                'id' => $id,
                'type' => $type
            )
        );

        return $this->render('citation/ajax_afficher.html.twig', array(
            'citations' => $result['paginator'],
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
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
            'update' => true,
            'session' => $session,
            'choices_episodes' => $citation->getEpisode()->getSerie()->getEpisodes(),
            'choices_personnages' => $this->sortArrayCollection($citation->getEpisode()->getSerie()->getPersonnages(), "getNomToSort")
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $citation = $form->getData();

            $manager = $this->getDoctrine()->getManager();
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

        return $this->render('citation/afficher.html.twig', array(
            'page' => $page,
            'citation' => $citation,
            'path_update' => $path_update,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire d'ajout d'une citation
     *
     * @Route("/{slug}/citation/ajouter/{page}", name="citation_ajouter")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
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
            'choices_personnages' => $this->sortArrayCollection($serie->getPersonnages(), "getNomToSort")
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
}
