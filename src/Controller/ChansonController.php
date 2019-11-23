<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
            'field' => 'Chanson.id',
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
            'active' => 'Chansons de ' . $serie->getNom()
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
     * @Route("/{slug}/chanson/afficher/{id}/{page}", name="chanson_afficher")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
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
                )) => 'Chansons de ' . $serie->getNom()
            ),
            'active' => 'Affichage de' . $this->getIdNom($chanson, 'chanson')
        );

        return $this->render('chanson/afficher.html.twig', array(
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
     * @Route("/{slug}/chanson/modifier/{id}/{page}", name="chanson_modifier")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
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

            return $this->redirectToRoute('chanson_liste', array(
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
                )) => 'Chansons de ' . $serie->getNom()
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
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

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($chanson);
            $manager->flush();

            return $this->redirectToRoute('chanson_liste', array(
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
                )) => 'Chansons de ' . $serie->getNom()
            ),
            'active' => "Ajout d'une chanson"
        );

        return $this->render('chanson/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
}
