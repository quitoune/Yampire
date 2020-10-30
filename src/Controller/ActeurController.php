<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Acteur;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Nationalite;
use App\Entity\Personnage;
use App\Entity\ActeurPersonnage;
use App\Form\ActeurType;
use App\Entity\Tag;

class ActeurController extends AppController
{
    /**
     * Liste des acteurs
     *
     * @Route("/acteur/liste/{page}", name="acteur_liste")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Acteur::class);
        
        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'Acteur',
            'field' => 'Acteur.id',
            'order' => 'ASC'
        ));
        $acteurs = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'acteur_liste',
            'route_params' => array()
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Acteurs'
        );

        return $this->render('acteur/index.html.twig', array(
            'acteurs' => $acteurs,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }
    
    /**
     * Affichage d'un acteur
     *
     * @Route("/acteur/ajax/afficher/{id}/{page}", name="ajax_afficher_acteur_fiche")
     * @ParamConverter("acteur", options={"mapping"={"id"="id"}})
     *
     * @param Acteur $acteur
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficher(Acteur $acteur, int $page)
    {
        return $this->render('acteur/ajax_afficher_fiche.html.twig', array(
            'acteur' => $acteur,
            'page' => $page
        ));
    }

    /**
     * Affichage d'un acteur
     *
     * @Route("/acteur/{slug}/afficher/{page}", name="acteur_afficher")
     *
     * @ParamConverter("acteur", options={"mapping"={"slug"="slug"}})
     *
     * @param Acteur $acteur
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Acteur $acteur, int $page = 1)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('acteur_liste', array(
                    'page' => $page
                )) => 'Acteurs'
            ),
            'active' => 'Affichage de' . $this->getIdNom($acteur, 'acteur')
        );

        return $this->render('acteur/afficher.html.twig', array(
            'acteur' => $acteur,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire de modification d'un acteur
     *
     * @Route("/acteur/{slug}/modifier/{page}", name="acteur_modifier")
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param Acteur $acteur
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, Acteur $acteur, int $page = 1)
    {
        $repo_nat = $this->getDoctrine()->getRepository(Nationalite::class);
        $repo_perso = $this->getDoctrine()->getRepository(Personnage::class);
        $repo_act_perso = $this->getDoctrine()->getRepository(ActeurPersonnage::class);
        $form = $this->createForm(ActeurType::class, $acteur, array(
            'update' => true,
            'feminin' => ($acteur->getSexe() ? true : false)
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $acteur = $form->getData();

            if(isset($request->request->all()["acteur"]["nationalites"])){
                $nationalites = $request->request->all()["acteur"]["nationalites"];
            } else {
                $nationalites = array();
            }
            
            if(isset($request->request->all()["acteur"]["acteurPersonnages"])){
                $acteurPersonnages = $request->request->all()["acteur"]["acteurPersonnages"];
            } else {
                $acteurPersonnages = array();
            }
            $actPersoId = array();
            
            foreach ($acteurPersonnages as $actPersos){
                if(isset($actPersos['id'])){
                    $actPersoId[] = $actPersos['id'];
                }
            }
            
            foreach ($acteur->getNationalites() as $nat) {
                if(!in_array($nat->getId(), $nationalites)){
                    $acteur->removeNationalite($nat);
                }
            }
            
            foreach ($nationalites as $nats){
                $acteur->addNationalite($repo_nat->findOneBy(array('id' => $nats)));
            }
            
            $manager = $this->getDoctrine()->getManager();
            
            foreach ($acteur->getActeurPersonnages() as $actPerso){
                if(!in_array($actPerso->getId(), $actPersoId)){
                    $RAW_QUERY = 'DELETE FROM `acteur_personnage` WHERE `acteur_personnage`.`id` = :id';
                    
                    $statement = $manager->getConnection()->prepare($RAW_QUERY);
                    // Set parameters
                    $statement->bindValue('id', $actPerso->getId());
                    $statement->execute();
                }
            }
            
            foreach ($acteurPersonnages as $actPersos){
                $acteurPersonnage = new ActeurPersonnage();
                
                if(!isset($actPersos['id'])){
                    $acteurPersonnage->setActeur($acteur);
                } else {
                    $acteurPersonnage = $repo_act_perso->findOneBy(array("id" => $actPersos['id']));
                }
                
                $perso = $repo_perso->findOneBy(array("id" => $actPersos['personnage']));
                $acteurPersonnage->setPersonnage($perso);
                $acteurPersonnage->setPrincipal($actPersos['principal']);
                $manager->persist($acteurPersonnage);
            }

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($acteur);
            $manager->flush();

            return $this->redirectToRoute('acteur_afficher', array(
                'slug' => $acteur->getSlug(),
                'page' => $page
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('acteur_liste', array(
                    'page' => $page
                )) => 'Acteurs'
            ),
            'active' => 'Modification de' . $this->getIdNom($acteur, 'acteur')
        );
        
        $nats = $repo_nat->findBy(array(), array('nom_feminin' => 'ASC'));
        $nationalites = array();
        
        foreach($nats as $nat){
            $nationalites[$nat->getId()] = $nat->getNomFeminin();
        }
        
        $default_nats = $acteur->getNationalites();
        $default_nationalites = array();
        
        foreach($default_nats as $default_nat){
            $default_nationalites[] = $default_nat->getId();
        }
        
        $opt = array(
            "multiple" => "true",
            "class" => "form-control",
            "id" => "acteur_nationalites",
            "default" => $default_nationalites
        );
        
        $select_personnage = array();
        $personnages = $this->getDoctrine()
        ->getRepository(Personnage::class)
        ->findBy(array(), array(
            'nom' => 'ASC',
            'prenom' => 'ASC',
            'prenom_usage' => 'ASC'
        ));
        
        $opt_perso = array(
            "class" => "form-control",
            "id" => "acteur_acteurPersonnages_INDEX_personnage",
            "default" => 1
        );
        
        $index = 0;
        foreach ($personnages as $perso){
            if($index == 0){
                $opt_perso["default"] = $perso->getId();
            }
            $select_personnage[$perso->getId()] = $perso->getNomComplet();
            $index++;
        }

        return $this->render('acteur/modifier.html.twig', array(
            'nationalites' => $nationalites,
            'opt' => $opt,
            'personnages' => $select_personnage,
            'opt_perso' => $opt_perso,
            'form' => $form->createView(),
            'acteur' => $acteur,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire d'ajout d'un acteur
     *
     * @Route("/acteur/ajouter/{page}", name="acteur_ajouter")
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, int $page = 1)
    {
        $repo_nat = $this->getDoctrine()->getRepository(Nationalite::class);
        $repo_perso = $this->getDoctrine()->getRepository(Personnage::class);
        $acteur = new Acteur();

        $form = $this->createForm(ActeurType::class, $acteur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $acteur = $form->getData();
            $full_name = $request->request->all()["acteur"]["prenom"] . " " . $request->request->all()["acteur"]["nom"];
            $slug = $this->createSlug($full_name, 'Acteur');
            
            $tag = new Tag();
            $tag->setNom(str_replace('_', ' ', $slug));
            
            $acteur->setSlug($slug);
            $acteur->setTag($tag);

            if(isset($request->request->all()["acteur"]["nationalites"])){
                foreach ($request->request->all()["acteur"]["nationalites"] as $nationalite){
                    $nat = $repo_nat->findOneBy(array("id" => $nationalite));
                    $acteur->addNationalite($nat);
                    $nat->addActeur($acteur);
                }
            }

            $manager = $this->getDoctrine()->getManager();
            
            if(isset($request->request->all()["acteur"]["acteurPersonnages"])){
                foreach ($request->request->all()["acteur"]["acteurPersonnages"] as $personnage){
                    $acteurPersonnage = new ActeurPersonnage();
                    $acteurPersonnage->setActeur($acteur);
                    $acteurPersonnage->setPersonnage($repo_perso->findOneBy(array("id" => $personnage['personnage'])));
                    $acteurPersonnage->setPrincipal($personnage['principal']);
                    $manager->persist($acteurPersonnage);
                }
            }
            
            $manager->persist($tag);
            $manager->persist($acteur);
            $manager->flush();

            return $this->redirectToRoute('acteur_afficher', array(
                'slug' => $slug,
                'page' => $page
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('acteur_liste', array(
                    'page' => $page
                )) => 'Acteurs'
            ),
            'active' => "Ajout d'un acteur"
        );
        
        $select_personnage = array();
        $personnages = $this->getDoctrine()
            ->getRepository(Personnage::class)
            ->findBy(array(), array(
            'nom' => 'ASC',
            'prenom' => 'ASC',
            'prenom_usage' => 'ASC'
        ));
            
        $opt_perso = array(
            "class" => "form-control",
            "id" => "acteur_acteurPersonnages_INDEX_personnage",
            "default" => 1
        );
        
        $index = 0;
        foreach ($personnages as $perso){
            if($index == 0){
                $opt_perso["default"] = $perso->getId();
            }
            $select_personnage[$perso->getId()] = $perso->getNomComplet();
            $index++;
        }
        
        $nats = $repo_nat->findBy(array(), array('nom_feminin' => 'ASC'));
        $nationalites = array();
        
        foreach($nats as $nat){
            $nationalites[$nat->getId()] = $nat->getNomFeminin();
        }
        
        $opt = array(
            "class" => "form-control",
            "multiple" => "true",
            "id" => "acteur_nationalites",
            "default" => 1
        );
        
        return $this->render('acteur/ajouter.html.twig', array(
            'nationalites' => $nationalites,
            'opt' => $opt,
            'personnages' => $select_personnage,
            'opt_perso' => $opt_perso,
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
    
    /**
     * Affichage des notes d'un acteur
     *
     * @Route("/acteur/afficher_notes/{id}", name="acteur_afficher_notes")
     * @ParamConverter("acteur", options={"mapping"={"id"="id"}})
     *
     * @param Acteur $acteur
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxSeeNotes(Acteur $acteur)
    {
        $notes = $acteur->getNotes();
        
        return $this->render('acteur/see_notes.html.twig', array(
            'notes' => $notes,
            'acteur' => $acteur
        ));
    }
}
