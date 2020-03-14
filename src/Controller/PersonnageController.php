<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Personnage;
use App\Entity\Serie;
use App\Entity\PersonnageSerie;
use App\Entity\Episode;
use App\Entity\Saison;
use App\Entity\PersonnageSaison;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Acteur;
use App\Entity\ActeurPersonnage;
use App\Form\PersonnageType;
use App\Entity\Espece;

class PersonnageController extends AppController
{
    /**
     * Liste de tous les personnages
     *
     * @Route("/personnage/liste/{page}", name="perso_liste")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listePersonnage(int $page = 1)
    {        
        $repository = $this->getDoctrine()->getRepository(Personnage::class);
        
        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'Personnage',
            'field' => 'Personnage.id',
            'order' => 'ASC'
        ));
        $personnages = $paginator['paginator'];
        
        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'perso_liste',
            'route_params' => array(
                'page' => $page
            )
        );
        
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Personnages'
        );
        
        return $this->render('personnage/liste.html.twig', array(
            'personnages' => $personnages,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }
    
    /**
     * Liste des personnages
     *
     * @Route("/{slug}/personnage/liste/{page}", name="personnage_liste")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(Serie $serie, int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(PersonnageSerie::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'PersonnageSerie',
            'field' => 'personnage.id',
            'order' => 'ASC',
            'condition' => 'PersonnageSerie.serie = ' . $serie->getId(),
            'serie_id' => $serie->getId(),
            'jointure' => array(
                array(
                    'oldrepository' => 'PersonnageSerie',
                    'newrepository' => 'personnage'
                )
            )
        ));
        $personnageSeries = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'personnage_liste',
            'route_params' => array(
                'slug' => $serie->getSlug()
            )
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Personnages de ' . $serie->getNom($this->getVo("serie"))
        );

        return $this->render('personnage/index.html.twig', array(
            'personnageSeries' => $personnageSeries,
            'pagination' => $pagination,
            'paths' => $paths,
            'serie' => $serie
        ));
    }

    /**
     * Affichage d'un personnage
     *
     * @Route("/{slug}/personnage/ajax/afficher/{slug_perso}/{page}", name="ajax_afficher_personnage_fiche")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("personnage", options={"mapping"={"slug_perso"="slug"}})
     *
     * @param Personnage $personnage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficherFiche(Serie $serie, Personnage $personnage, int $page = 1)
    {
        $serieSaisons = $this->getSerieSaison($personnage);
        return $this->render('personnage/ajax_afficher_fiche.html.twig', array(
            'page' => $page,
            'serie' => $serie,
            'personnage' => $personnage,
            'serieSaisons' => $serieSaisons
        ));
    }
    
    /**
     * Retourne les séries et les saisons dans lesquelles un personnage a joué
     * @param Personnage $personnage
     * @return array[][]
     */
    private function getSerieSaison(Personnage $personnage, bool $fiche = true){
        $serie_saison = array();
        
        $query  = "SELECT Se.id as serie_id, Se.nom as serie_nom, ";
        $query .= "PSe.principal as serie_role, Sa.numero_saison as saison_numero, ";
        $query .= "Sa.id as saison_id, PSa.principal as saison_role ";
        $query .= "FROM `serie` Se, `personnage_serie` PSe, `personnage_saison` PSa, ";
        $query .= "`saison` Sa WHERE Se.id = PSe.serie_id AND PSa.personnage_id = PSe.personnage_id ";
        $query .= "AND Sa.id = PSa.saison_id AND Sa.serie_id = Se.id ";
        $query .= "AND PSe.personnage_id = " . $personnage->getId();
        
        $em = $this->getDoctrine()->getManager();
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        
        $seriesaisons = $statement->fetchAll();
        if($fiche){
            foreach($seriesaisons as $seriesaison){
                if(!isset($serie_saison[$seriesaison['serie_id']])){
                    
                    $serie_saison[$seriesaison['serie_id']] = array(
                        'nom'     => $seriesaison['serie_nom'],
                        'role'    => $seriesaison['serie_role'],
                        'saisons' => array()
                    );
                }
                $serie_saison[$seriesaison['serie_id']]['saisons'][$seriesaison['saison_id']] = array(
                    'nom'  => 'Saison ' . $seriesaison['saison_numero'],
                    'role' => $seriesaison['saison_role']
                );
            }
        } else {
            foreach($seriesaisons as $seriesaison){
                if(!isset($serie_saison[$seriesaison['serie_nom']])){
                    
                    $serie_saison[$seriesaison['serie_nom']] = array();
                }
                $serie_saison[$seriesaison['serie_nom']][$seriesaison['saison_id']] = 'Saison ' . $seriesaison['saison_numero'];
            }
        }
        
        return $serie_saison;
        
    }

    /**
     * Affichage d'un personnage
     *
     * @Route("/{slug}/personnage/{slug_perso}/afficher/{page}", name="personnage_afficher")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("personnage", options={"mapping"={"slug_perso"="slug"}})
     *
     * @param Personnage $personnage
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Serie $serie, Personnage $personnage, int $page = 1)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('personnage_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Personnages de ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => 'Affichage du' . $this->getIdNom($personnage, 'personnage')
        );

        return $this->render('personnage/afficher.html.twig', array(
            'personnage' => $personnage,
            'page' => $page,
            'paths' => $paths,
            'serie' => $serie
        ));
    }

    /**
     * Affichage des personnage d'une série / d'une saison / d'un episode
     *
     * @Route("/personnage/ajax/afficher/{type}/{id}/{page}", name="ajax_afficher_personnages")
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
            case 'espece':
                $repository = $this->getDoctrine()->getRepository(Espece::class);
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

        $params = array(
            'order' => 'ASC',
            'page' => $page
        );

        switch ($type) {
            case 'episode':
                $repo = $this->getDoctrine()->getRepository(Personnage::class);
                $params['field'] = 'Personnage.nom ASC, Personnage.prenom ASC, Personnage.prenom_usage';
                $params['repository'] = 'Personnage';
                $params['condition'] = 'episodes.id = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'Personnage',
                        'newrepository' => 'episodes'
                    )
                );

                break;
            case 'espece':
                $repo = $this->getDoctrine()->getRepository(Personnage::class);
                $params['field'] = 'Personnage.nom ASC, Personnage.prenom ASC, Personnage.prenom_usage';
                $params['repository'] = 'Personnage';
                $params['condition'] = 'espece.id = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'Personnage',
                        'newrepository' => 'espece'
                    )
                );

                break;
            case 'saison':
                $repo = $this->getDoctrine()->getRepository(PersonnageSaison::class);
                $params['field'] = 'personnage.nom ASC, personnage.prenom ASC, personnage.prenom_usage';
                $params['repository'] = 'PersonnageSaison';
                $params['condition'] = 'PersonnageSaison.saison = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'PersonnageSaison',
                        'newrepository' => 'personnage'
                    )
                );

                break;
            case 'serie':
                $repo = $this->getDoctrine()->getRepository(PersonnageSerie::class);
                $params['field'] = 'personnage.nom ASC, personnage.prenom ASC, personnage.prenom_usage';
                $params['repository'] = 'PersonnageSerie';
                $params['condition'] = 'PersonnageSerie.serie = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'PersonnageSerie',
                        'newrepository' => 'personnage'
                    )
                );

                break;
        }

        $nbr_max_ajax = $this->getNbrMaxAjax();
        $result = $repo->findAllElements($page, $nbr_max_ajax, $params);

        $pagination = array(
            'page' => $page,
            'route' => 'ajax_afficher_personnages',
            'pages_count' => ceil($result['nombre'] / $nbr_max_ajax),
            'nb_elements' => $result['nombre'],
            'route_params' => array(
                'id' => $id,
                'type' => $type
            )
        );

        return $this->render('personnage/ajax_afficher.html.twig', array(
            'personnages' => $result['paginator'],
            'type' => $type,
            'objet' => $objet,
            'pagination' => $pagination
        ));
    }

    /**
     * Formulaire de modification d'un personnage
     *
     * @Route("/{slug}/personnage/{slug_perso}/modifier/{page}", name="personnage_modifier")
     * @ParamConverter("serie", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("personnage", options={"mapping"={"slug_perso"="slug"}})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
     *
     * @param Request $request
     * @param Personnage $personnage
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, Serie $serie, Personnage $personnage, int $page = 1)
    {
        $repo_acteur = $this->getDoctrine()->getRepository(Acteur::class);
        $repo_act_perso = $this->getDoctrine()->getRepository(ActeurPersonnage::class);

        $form = $this->createForm(PersonnageType::class, $personnage, array(
            'update' => true
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personnage = $form->getData();

            if (isset($request->request->all()["personnage"]["personnageActeurs"])) {
                $personnageActeurs = $request->request->all()["personnage"]["personnageActeurs"];
            } else {
                $personnageActeurs = array();
            }
            $actPersoId = array();

            foreach ($personnageActeurs as $actPersos) {
                if (isset($actPersos['id'])) {
                    $actPersoId[] = $actPersos['id'];
                }
            }

            $manager = $this->getDoctrine()->getManager();

            foreach ($personnage->getActeurPersonnages() as $actPerso) {
                if (! in_array($actPerso->getId(), $actPersoId)) {
                    $RAW_QUERY = 'DELETE FROM `acteur_personnage` WHERE `acteur_personnage`.`id` = :id';

                    $statement = $manager->getConnection()->prepare($RAW_QUERY);
                    // Set parameters
                    $statement->bindValue('id', $actPerso->getId());
                    $statement->execute();
                }
            }

            foreach ($personnageActeurs as $actPersos) {
                $acteurPersonnage = new ActeurPersonnage();

                if (! isset($actPersos['id'])) {
                    $acteurPersonnage->setPersonnage($personnage);
                } else {
                    $acteurPersonnage = $repo_act_perso->findOneBy(array(
                        "id" => $actPersos['id']
                    ));
                }

                $act = $repo_acteur->findOneBy(array(
                    "id" => $actPersos['acteur']
                ));
                $acteurPersonnage->setActeur($act);
                $acteurPersonnage->setPrincipal($actPersos['principal']);
                $manager->persist($acteurPersonnage);
            }

            $manager->persist($personnage);
            $manager->flush();

            return $this->redirectToRoute('personnage_afficher', array(
                'page' => $page,
                'slug' => $serie->getSlug(),
                'slug_perso' => $personnage->getSlug()
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('personnage_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Personnages de ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => 'Modification du' . $this->getIdNom($personnage, 'personnage')
        );

        $select_acteur = array();
        $acteurs = $repo_acteur->findBy(array(), array(
            'nom' => 'ASC',
            'prenom' => 'ASC'
        ));

        $opt_act = array(
            "class" => "form-control",
            "id" => "personnage_personnageActeurs_INDEX_acteur",
            "default" => 1
        );

        $index = 0;
        foreach ($acteurs as $act) {
            if ($index == 0) {
                $opt_act["default"] = $act->getId();
            }
            $select_acteur[$act->getId()] = $act->getPrenom() . " " . $act->getNom();
            $index ++;
        }

        return $this->render('personnage/modifier.html.twig', array(
            'form' => $form->createView(),
            'personnage' => $personnage,
            'acteurs' => $select_acteur,
            'opt_act' => $opt_act,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * /**
     * Formulaire d'ajout d'un personnage
     *
     * @Route("/{slug}/personnage/ajouter/{page}", name="personnage_ajouter")
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
     *
     * @param Request $request
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, Serie $serie, int $page = 1)
    {
        $repo_serie = $this->getDoctrine()->getRepository(Serie::class);
        $repo_saison = $this->getDoctrine()->getRepository(Saison::class);
        $repo_acteur = $this->getDoctrine()->getRepository(Acteur::class);
        $repo_episode = $this->getDoctrine()->getRepository(Episode::class);

        $personnage = new Personnage();

        $form = $this->createForm(PersonnageType::class, $personnage);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $personnage = $form->getData();
            
            $full_name = " " . $request->request->all()["personnage"]["nom"];
            if(isset($request->request->all()["personnage"]["prenom_usage"]) && $request->request->all()["personnage"]["prenom_usage"]){
                $full_name = $request->request->all()["personnage"]["prenom_usage"] . $full_name;
            } else {
                $full_name = $request->request->all()["personnage"]["prenom"] . $full_name;
            }
            $slug = $this->createSlug($full_name, 'Personnage');
            echo $full_name . " ->" . $slug;die();
            $personnage->setSlug($slug);

            if (isset($request->request->all()["personnage"]["personnageSeries"])) {
                $form_personnageSeries = $request->request->all()['personnage']['personnageSeries'];
            } else {
                $form_personnageSeries = array();
            }

            if (isset($request->request->all()["personnage"]["personnageSaisons"])) {
                $form_personnageSaisons = $request->request->all()['personnage']['personnageSaisons'];
            } else {
                $form_personnageSaisons = array();
            }

            if (isset($request->request->all()["personnage"]["personnageEpisodes"])) {
                $form_personnageEpisodes = $request->request->all()['personnage']['personnageEpisodes'];
            } else {
                $form_personnageEpisodes = array();
            }

            $manager = $this->getDoctrine()->getManager();

            // Ajout des épisodes
            foreach ($form_personnageEpisodes as $id_episode => $on) {
                $episode = $repo_episode->findOneBy(array(
                    'id' => $id_episode
                ));
                $personnage->addEpisode($episode);
                $episode->addPersonnage($personnage);
                $manager->persist($episode);
            }

            // Ajout des séries
            foreach ($form_personnageSeries as $id_serie => $infos) {
                $personnageSerie = new PersonnageSerie();
                $personnageSerie->setPersonnage($personnage);
                $personnageSerie->setSerie($repo_serie->findOneBy(array(
                    "id" => $id_serie
                )));
                $personnageSerie->setPrincipal($infos['principal']);
                $manager->persist($personnageSerie);
            }

            // Ajout des saisons
            foreach ($form_personnageSaisons as $id_saison => $info) {
                $personnageSaison = new PersonnageSaison();
                $personnageSaison->setPersonnage($personnage);
                $personnageSaison->setSaison($repo_saison->findOneBy(array(
                    "id" => $id_saison
                )));
                $personnageSaison->setPrincipal($info['principal']);
                $manager->persist($personnageSaison);
            }

            if (isset($request->request->all()["personnage"]["personnageActeurs"])) {
                foreach ($request->request->all()["personnage"]["personnageActeurs"] as $acteur) {
                    $acteurPersonnage = new ActeurPersonnage();
                    $acteurPersonnage->setPersonnage($personnage);
                    $acteurPersonnage->setActeur($repo_acteur->findOneBy(array(
                        "id" => $acteur['acteur']
                    )));
                    $acteurPersonnage->setPrincipal($acteur['principal']);
                    $manager->persist($acteurPersonnage);
                }
            }

            $manager->persist($personnage);
            $manager->flush();

            return $this->redirectToRoute('personnage_liste', array(
                'page' => $page,
                'slug' => $serie->getSlug()
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('personnage_liste', array(
                    'page' => $page,
                    'slug' => $serie->getSlug()
                )) => 'Personnages de ' . $serie->getNom($this->getVo("serie"))
            ),
            'active' => "Ajout d'un personnage"
        );

        $series = $this->getDoctrine()
            ->getRepository(Serie::class)
            ->findAll();

        $select_acteur = array();
        $acteurs = $repo_acteur->findBy(array(), array(
            'nom' => 'ASC',
            'prenom' => 'ASC'
        ));

        $opt_act = array(
            "class" => "form-control",
            "id" => "personnage_personnageActeurs_INDEX_acteur",
            "default" => 1
        );

        $index = 0;
        foreach ($acteurs as $act) {
            if ($index == 0) {
                $opt_act["default"] = $act->getId();
            }
            $select_acteur[$act->getId()] = $act->getPrenom() . " " . $act->getNom();
            $index ++;
        }

        return $this->render('personnage/ajouter.html.twig', array(
            'form' => $form->createView(),
            'series' => $series,
            'acteurs' => $select_acteur,
            'opt_act' => $opt_act,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Affichage des notes d'un personnage
     *
     * @Route("/personnage/afficher_notes/{id}", name="personnage_afficher_notes")
     * @ParamConverter("personnage", options={"mapping"={"id"="id"}})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ajaxSeeNotes(Personnage $personnage)
    {
        $notes = $personnage->getNotes();

        return $this->render('personnage/see_notes.html.twig', array(
            'notes' => $notes,
            'personnage' => $personnage
        ));
    }

    /**
     * Affichage des épisodes d'un personnage
     *
     * @Route("/personnage/afficher_episodes/{id}", name="personnage_afficher_episodes")
     * @ParamConverter("personnage", options={"mapping"={"id"="id"}})
     *
     * @param Personnage $personnage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxSeeEpisodes(Personnage $personnage)
    {
        $saisons = $this->getSerieSaison($personnage, false);

        return $this->render('episode/ajax_afficher_personnage.html.twig', array(
            'saisons' => $saisons,
            'personnage' => $personnage
        ));
    }
}
