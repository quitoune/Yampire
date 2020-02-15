<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Photo;
use App\Entity\Tag;
use App\Entity\Serie;

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
        $paginator = $repository->findAllElements($page, $nbr_max);
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
     * Affichage des photos associées à une série
     *
     * @Route("/photo/ajax/liste/{id}/{page}", name="ajax_afficher_photos")
     * 
     * @ParamConverter("serie", options={"mapping"={"id"="id"}})
     * 
     * @param Serie $serie
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficherPourSerie(Serie $serie, int $page = 1){
        $photos = $serie->getTag()->getPhotos();
        
        return $this->render('photo/ajax_afficher.html.twig', array(
            'photos' => $photos,
            'serie' => $serie
        ));
    }
    
    /**
     * Retourne les photos associées à une série
     * @param Serie $serie
     * @return array[][]
     */
    private function getPhotoPourSerie(Serie $serie){
        $photos = array();
        $sql_photos = array();
        
        $repository = $this->getDoctrine()->getRepository(Photo::class);
        
        $query  = "Select P.* From photo as P, photo_tag as PT, tag as T ";
        $query .= "WHERE P.id = PT.photo_id and T.id = PT.tag_id and T.id = " . $serie->getTag()->getId();
        
        $em = $this->getDoctrine()->getManager();
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        
        $sql_photos = $statement->fetchAll();
        foreach($sql_photos as $sql_photo){
            $obj_photo = $repository->findOneBy(array('id' => $sql_photo['id']));
            if(!is_null($obj_photo)){
                $photos[] = $obj_photo;
            }
        }
        
        return $photos;
    }
    
    /**
     * Supprimer un tag d'une photo
     * 
     * @Route("/photo/delete/{photo_id}/{tag_id}", name="photo_tag_supprimer")
     *
     * @ParamConverter("photo", options={"mapping"={"photo_id"="id"}})
     * @ParamConverter("tag", options={"mapping"={"tag_id"="id"}})
     * 
     * @param Photo $photo
     */
    public function supprimer(Photo $photo, Tag $tag){
        $photo->removeTag($tag);
        
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($photo);
        $manager->flush();
        
        return $this->render('photo/afficher_tag.html.twig', array(
            'photo' => $photo
        ));
    }
}
