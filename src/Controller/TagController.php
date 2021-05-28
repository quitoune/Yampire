<?php
namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tag;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TagType;

class TagController extends AppController
{

    /**
     * Liste des tags
     *
     * @Route("/tag/liste/{page}", name="tag_liste")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Tag::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max, array(
            'repository' => 'Tag',
            'field' => 'Tag.nom',
            'order' => 'ASC'
        ));
        $tags = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'tag_liste',
            'route_params' => array()
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Tags'
        );

        return $this->render('tag/index.html.twig', array(
            'tags' => $tags,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }
    
    /**
     * Affichage d'un tag
     *
     * @Route("/tag/afficher/{id}/{page}", name="tag_afficher")
     *
     * @param Tag $tag
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Tag $tag, int $page = 1)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('tag_liste', array(
                    'page' => $page
                )) => 'Tags'
            ),
            'active' => 'Affichage ' . $this->getIdNom($tag, 'tag')
        );
        
        return $this->render('tag/afficher.html.twig', array(
            'tag' => $tag,
            'page' => $page,
            'paths' => $paths
        ));
    }
    
    /**
     * Affichage d'un tag
     *
     * @Route("/tag/ajax/afficher/{id}/{page}", name="ajax_afficher_tag_fiche")
     *
     * @param Tag $tag
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficherFiche(Tag $tag, int $page = 1)
    {
        return $this->render('tag/ajax_afficher_fiche.html.twig', array(
            'tag' => $tag,
            'page' => $page
        ));
    }
    
    /**
     * Formulaire d'ajout d'un épisode
     *
     * @Route("/tag/ajouter/{page}", name="tag_ajouter")
     * 
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, int $page = 1)
    {
        $tag = new Tag();
        
        $form = $this->createForm(TagType::class, $tag);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $tag = $form->getData();
            
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($tag);
            $manager->flush();
            
            return $this->redirectToRoute('tag_liste');
        }
        
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('tag_liste', array(
                    'page' => $page
                )) => 'Tags'
            ),
            'active' => "Ajout d'un tag"
        );
        
        return $this->render('tag/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
    
    /**
     * Formulaire de modification d'un tag
     *
     * @Route("/tag/modifier/{id}/{page}", name="tag_modifier")
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param Tag $tag
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, Tag $tag, int $page = 1)
    {
        $form = $this->createForm(TagType::class, $tag, array(
            'update' => true
        ));
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $tag = $form->getData();
            
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($tag);
            $manager->flush();
            
            return $this->redirectToRoute('tag_afficher', array(
                'page' => $page,
                'id' => $tag->getId()
            ));
        }
        
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('tag_liste', array(
                    'page' => $page
                )) => 'Tags'
            ),
            'active' => 'Modification de' . $this->getIdNom($tag, 'tag')
        );
        
        return $this->render('tag/modifier.html.twig', array(
            'form' => $form->createView(),
            'tag' => $tag,
            'page' => $page,
            'paths' => $paths
        ));
    }
}
