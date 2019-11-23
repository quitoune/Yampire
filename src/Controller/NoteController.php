<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Acteur;
use App\Entity\Episode;
use App\Entity\Personnage;
use App\Entity\Saison;
use App\Entity\Serie;
use App\Entity\Note;
use Symfony\Component\HttpFoundation\Request;
use App\Form\NoteType;

class NoteController extends AppController
{
    /**
     * Affichage des notes d'un personnage / d'un acteur / d'un episode
     *
     * @Route("/note/ajax/afficher/{type}/{id}/{page}", name="ajax_afficher_notes")
     *
     * @param int $id
     * @param string $type
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficher(int $id, string $type, int $page = 1)
    {
        switch ($type) {
            case 'acteur':
                $repository = $this->getDoctrine()->getRepository(Acteur::class);
                break;
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

        $repo = $this->getDoctrine()->getRepository(Note::class);

        $params = array(
            'repository' => 'Note',
            'order' => 'ASC',
            'page' => $page
        );

        switch ($type) {
            case 'acteur':
                $params['field'] = 'Note.id';
                $params['condition'] = 'Note.acteur = ' . $id;
                break;
            case 'personnage':
                $params['field'] = 'Note.id';
                $params['condition'] = 'Note.personnage = ' . $id;
                break;
            case 'episode':
                $params['field'] = 'Note.id';
                $params['condition'] = 'Note.episode = ' . $id;
                break;
            case 'saison':
                $params['field'] = 'episode.numero_episode ASC, Note.id';
                $params['condition'] = 'episode.saison = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'Note',
                        'newrepository' => 'episode'
                    )
                );
                break;
            case 'serie':
                $params['field'] = 'episode.numero_production ASC, Note.id';
                $params['condition'] = 'episode.serie = ' . $id;
                $params['jointure'] = array(
                    array(
                        'oldrepository' => 'Note',
                        'newrepository' => 'episode'
                    )
                );
                break;
        }

        $nbr_max_ajax = $this->getNbrMaxAjax();
        $result = $repo->findAllElements($page, $nbr_max_ajax, $params);

        $pagination = array(
            'page' => $page,
            'route' => 'ajax_afficher_notes',
            'pages_count' => ceil($result['nombre'] / $nbr_max_ajax),
            'nb_elements' => $result['nombre'],
            'route_params' => array(
                'id' => $id,
                'type' => $type
            )
        );

        return $this->render('note/ajax_afficher.html.twig', array(
            'notes' => $result['paginator'],
            'type' => $type,
            'objet' => $objet,
            'pagination' => $pagination
        ));
    }

    /**
     * Formulaire de modification d'une note dans une modal
     *
     * @Route("/note/ajax/ajouter/{type}/{id}", name="note_ajax_ajouter")
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
     *
     * @param Request $request
     * @param int $id
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAjouter(Request $request, int $id, string $type)
    {
        $note = new Note();

        $methode = 'set' . ucwords($type);

        switch ($type) {
            case 'acteur':
                $repository = $this->getDoctrine()->getRepository(Acteur::class);
                break;
            case 'episode':
                $repository = $this->getDoctrine()->getRepository(Episode::class);
                break;
            case 'personnage':
                $repository = $this->getDoctrine()->getRepository(Personnage::class);
                break;
            case 'serie':
                $repository = $this->getDoctrine()->getRepository(Serie::class);
                break;
        }
        $objet = $repository->findOneBy(array('id' => $id));

        if ($type != 'serie') {
            $note->{$methode}($objet);
        }

        $form = $this->createForm(NoteType::class, $note);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $note = $form->getData();

            $note->setDateCreation(new \DateTime());
            $note->setUtilisateur($this->getUtilisateur());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($note);
            $manager->flush();

            return $this->json(array(
                'statut' => true
            ));
        }

        return $this->render('note/ajax_ajouter.html.twig', array(
            'form' => $form->createView(),
            'objet' => $objet,
            'type' => $type
        ));
    }

    /**
     * Modification d'une note d'un personnage / d'un acteur / d'un épisode
     *
     * @Route("/note/ajax/modifier/{note_id}/{type}/{id}/{page}", name="note_ajax_modifier")
     * @ParamConverter("note", options={"mapping"={"note_id"="id"}})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
     *
     * @param Request $request
     * @param Note $note
     * @param int $id
     * @param string $type
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxModifier(Request $request, Note $note, int $id, string $type, int $page  = 1)
    {
        $form = $this->createForm(NoteType::class, $note, array(
            'update' => true
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($note);
            $manager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json(array(
                    'statut' => true
                ));
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('note/ajax_modifier.html.twig', array(
                'form' => $form->createView(),
                'note' => $note,
                'id' => $id, 
                'type' => $type, 
                'page' => $page
            ));
        }
    }
}
