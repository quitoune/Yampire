<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Serie;
use App\Entity\Saison;
use Symfony\Component\HttpFoundation\Request;
use App\Form\SaisonType;

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
     * @Route("/saison/ajax/afficher/{id}", name="ajax_afficher_saison_fiche")
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
     * @Route("/saison/ajax/afficher/{type}/{id}/{page}", name="ajax_afficher_saisons")
     *
     * @param int $id
     * @param string $type
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficher(int $id, string $type, int $page = 1)
    {
        $repo = $this->getDoctrine()->getRepository(Saison::class);
        $repository = $this->getDoctrine()->getRepository(Serie::class);
        $objets = $repository->findById($id);
        $objet = $objets[0];

        $params = array(
            'repository' => 'Saison',
            'field' => 'Saison.numero_saison',
            'condition' => 'Saison.serie = ' . $id,
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
                'id' => $id,
                'type' => $type
            )
        );

        return $this->render('saison/ajax_afficher.html.twig', array(
            'saisons' => $result['paginator'],
            'type' => $type,
            'objet' => $objet,
            'pagination' => $pagination
        ));
    }

    /**
     * Formulaire d'affichage d'une saison
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
     * Formulaire de modifcation d'une saison
     *
     * @Route("/{slug}/saison/modifier/{id}/{page}", name="saison_modifier")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
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

            $manager = $this->getDoctrine()->getManager();
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
}
