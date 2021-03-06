<?php
namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Photo;
use App\Entity\Tag;
use App\Entity\Serie;
use App\Entity\Acteur;
use App\Entity\Episode;
use App\Entity\Espece;
use App\Entity\Personnage;
use App\Entity\Saison;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PhotoType;

class PhotoController extends AppController
{

    /**
     * Liste des photos
     *
     * @Route("/photo/liste/{page}", name="photo_liste")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Photo::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'Photo',
            'field' => 'Photo.id',
            'order' => 'ASC'
        ));
        $photos = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'photo_liste',
            'route_params' => array()
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Photos'
        );

        return $this->render('photo/index.html.twig', array(
            'photos' => $photos,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }

    /**
     * Affichage d'une photo
     *
     * @Route("/photo/afficher/{id}/{page}", name="photo_afficher")
     *
     * @ParamConverter("photo", options={"mapping"={"id"="id"}})
     *
     * @param Photo $photo
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Photo $photo, int $page = 1)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('photo_liste', array(
                    'page' => $page
                )) => 'Photos'
            ),
            'active' => 'Affichage de ' . $this->getIdNom($photo, 'photo')
        );

        return $this->render('photo/afficher.html.twig', array(
            'photo' => $photo,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Affichage des photos associées à un élément acteur / épisode / espèce / personnage / saison / série
     *
     * @Route("/photo/ajax/liste/{id}/{type}/{page}", name="ajax_afficher_photos")
     *
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficher(int $id, string $type, int $page = 1)
    {
        $objet = $this->getElement($id, $type);

        if($type == 'tag'){
            $id_tag = $objet->getId();
        } else {
            if (is_null($objet->getTag())) {
                $id_tag = 0;
            } else {
                $id_tag = $objet->getTag()->getId();
            }
        }

        $repo = $this->getDoctrine()->getRepository(Photo::class);

        $nbr_max_ajax = $this->getNbrMaxAjax();
        $paginator = $repo->findAllElements($page, $nbr_max_ajax, array(
            'repository' => 'Photo',
            'field' => 'Photo.id',
            'order' => 'ASC',
            'condition' => 'tags = ' . $id_tag,
            'jointure' => array(
                array(
                    'oldrepository' => 'Photo',
                    'newrepository' => 'tags'
                )
            )
        ));

        $pagination = array(
            'page' => $page,
            'pages_count' => ceil($paginator['nombre'] / $nbr_max_ajax),
            'nb_elements' => $paginator['nombre'],
            'route' => 'ajax_afficher_photos',
            'route_params' => array(
                'id' => $objet->getId(),
                'type' => $type,
                'page' => $page
            )
        );

        return $this->render('photo/ajax_afficher.html.twig', array(
            'photos' => $paginator['paginator'],
            'objet' => $objet,
            'type' => $type,
            'pagination' => $pagination
        ));
    }
    
    /**
     * Ajout des photos associées à un élément acteur / épisode / espèce / personnage / saison / série
     *
     * @Route("/photo/ajax/ajouter/{id}/{type}", name="ajax_ajouter_photos")
     *
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAjouter(Request $request, int $id, string $type)
    {
        $objet = $this->getElement($id, $type);
        $tag = $objet->getTag();
        
        if ($request->isMethod('post')) {
            $data = $request->request->all();
            $repo = $this->getDoctrine()->getRepository(Photo::class);
            $manager = $this->getDoctrine()->getManager();
            
            foreach ($data['photos'] as $image) {
                $photo = $repo->findOneBy(array(
                    'id' => $image
                ));
                if(!is_null($photo)){
                    $tag->addPhoto($photo);
                    $manager->persist($photo);
                }
            }
            $manager->flush();
            
            return $this->json(array(
                'statut' => true
            ));
        }
        
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $sql = 'SELECT * FROM `photo_tag` WHERE `tag_id` = ' . $tag->getId();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $tag_photos = array();
        foreach($stmt->fetchAll() as $photo){
            $tag_photos[] = $photo['photo_id'];
        }
        
        $all_photos = $this->getDoctrine()->getRepository(Photo::class)->findAll();
        $photos = array();
        foreach($all_photos as $photo){
            if(!in_array($photo->getId(), $tag_photos)){
                $photos[] = $photo;
            }
        }
        
        return $this->render('photo/ajax_ajouter.html.twig', array(
            'photos' => $photos,
            'objet' => $objet,
            'type' => $type
        ));
    }

    /**
     * Suppression des photos associées à un élément acteur / épisode / espèce / personnage / saison / série
     *
     * @Route("/photo/ajax/supprimer/{id}/{type}/{page}", name="ajax_supprimer_photos")
     *
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxSupprimer(Request $request, int $id, string $type, int $page = 1)
    {
        $objet = $this->getElement($id, $type);
        
        $tag = $objet->getTag();
        
        if ($request->isMethod('post')) {
            $data = $request->request->all();
            $repo = $this->getDoctrine()->getRepository(Photo::class);
            $manager = $this->getDoctrine()->getManager();
            
            foreach ($data['photos'] as $image) {
                $photo = $repo->findOneBy(array(
                    'id' => $image
                ));
                if(!is_null($photo)){
                    $tag->removePhoto($photo);
                    $manager->persist($photo);
                }
            }
            $manager->flush();
            
            return $this->json(array(
                'statut' => true
            ));
        }

        $photos = $tag->getPhotos();

        return $this->render('photo/ajax_supprimer.html.twig', array(
            'photos' => $photos,
            'objet' => $objet,
            'type' => $type
        ));
    }

    /**
     * Supprimer un tag d'une photo
     *
     * @Route("/photo/afficher_tag/{photo_id}", name="photo_tag_afficher")
     *
     * @ParamConverter("photo", options={"mapping"={"photo_id"="id"}})
     *
     * @param Photo $photo
     */
    public function afficherTag(Photo $photo)
    {
        return $this->render('photo/afficher_tag.html.twig', array(
            'photo' => $photo
        ));
    }

    /**
     * Supprimer un tag d'une photo
     *
     * @Route("/photo/supprimer/{photo_id}/{tag_id}", name="photo_tag_supprimer")
     *
     * @ParamConverter("photo", options={"mapping"={"photo_id"="id"}})
     * @ParamConverter("tag", options={"mapping"={"tag_id"="id"}})
     *
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Photo $photo
     */
    public function supprimerTag(Photo $photo, Tag $tag)
    {
        $photo->removeTag($tag);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($photo);
        $manager->flush();

        return $this->render('photo/afficher_tag.html.twig', array(
            'photo' => $photo
        ));
    }

    /**
     * Ajouter un tag à une photo
     *
     * @Route("/photo/ajouter/{photo_id}", name="photo_tag_ajouter")
     *
     * @ParamConverter("photo", options={"mapping"={"photo_id"="id"}})
     *
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Photo $photo
     */
    public function ajouterTag(Request $request, Photo $photo)
    {
        $form = $this->createForm(PhotoType::class, $photo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $add_tags = $request->request->all()['photo']['tags'];

            $repo = $this->getDoctrine()->getRepository(Tag::class);

            $manager = $this->getDoctrine()->getManager();
            foreach ($add_tags as $add_tag) {
                if (is_numeric($add_tag)) {
                    $photo->addTag($repo->findOneBy(array(
                        'id' => $add_tag
                    )));
                } else {
                    $tag = new Tag();
                    $tag->setNom($add_tag);
                    $manager->persist($tag);
                    $photo->addTag($tag);
                }
            }
            $manager->persist($photo);
            $manager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json(array(
                    'statut' => true
                ));
            }
        }

        $photo_tags = $photo->getTags();

        $all_tags = $this->getDoctrine()
            ->getRepository(Tag::class)
            ->findAll();

        $tags = array();

        foreach ($all_tags as $all_tag) {
            if (! $photo_tags->contains($all_tag)) {
                $tags[] = $all_tag;
            }
        }

        return $this->render('photo/ajouter_tag.html.twig', array(
            'form' => $form->createView(),
            'photo' => $photo,
            'tags' => $tags
        ));
    }
    
    public function getElement(int $id, string $type)
    {
        switch ($type) {
            case 'acteur':
                $repository = $this->getDoctrine()->getRepository(Acteur::class);
                break;
            case 'episode':
                $repository = $this->getDoctrine()->getRepository(Episode::class);
                break;
            case 'espece':
                $repository = $this->getDoctrine()->getRepository(Espece::class);
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
            case 'tag':
                $repository = $this->getDoctrine()->getRepository(Tag::class);
                break;
        }
        
        $objet = $repository->findOneBy(array(
            'id' => $id
        ));
        
        return $objet;
    }
}
