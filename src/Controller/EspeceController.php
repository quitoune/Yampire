<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Espece;
use Symfony\Component\HttpFoundation\Request;
use App\Form\EspeceType;
use App\Entity\Tag;

class EspeceController extends AppController
{
    /**
     * Liste des espèces
     *
     * @Route("/espece/liste/{page}", name="espece_liste")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Espece::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max);
        $especes = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'espece_liste',
            'route_params' => array()
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Espèces'
        );

        return $this->render('espece/index.html.twig', array(
            'especes' => $especes,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }
    
    /**
     * Affichage d'une espece
     *
     * @Route("/espece/ajax/afficher/{id}/{page}", name="ajax_afficher_espece_fiche")
     * @ParamConverter("espece", options={"mapping"={"id"="id"}})
     *
     * @param Espece $espece
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAfficherFiche(Espece $espece, int $page)
    {
        return $this->render('espece/ajax_afficher_fiche.html.twig', array(
            'espece' => $espece,
            'page' => $page
        ));
    }

    /**
     * Affichage d'une espèce
     *
     * @Route("/espece/{slug}/afficher/{page}", name="espece_afficher")
     *
     * @param Espece $espece
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function afficher(Espece $espece, int $page = 1)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('espece_liste', array(
                    'page' => $page
                )) => 'Espèces'
            ),
            'active' => 'Affichage de' . $this->getIdNom($espece, 'espece')
        );

        return $this->render('espece/afficher.html.twig', array(
            'page' => $page,
            'espece' => $espece,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire de modification d'une espèce
     *
     * @Route("/espece/{slug}/modifier/{page}", name="espece_modifier")
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param Espece $espece
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifier(Request $request, Espece $espece, int $page = 1)
    {
        $form = $this->createForm(EspeceType::class, $espece, array(
            'update' => true
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $espece = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($espece);
            $manager->flush();

            return $this->redirectToRoute('espece_afficher', array(
                'slug' => $espece->getSlug(),
                'page' => $page
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('espece_liste', array(
                    'page' => $page
                )) => 'Espèces'
            ),
            'active' => 'Modification de' . $this->getIdNom($espece, 'espece')
        );

        return $this->render('espece/modifier.html.twig', array(
            'form' => $form->createView(),
            'espece' => $espece,
            'page' => $page,
            'paths' => $paths
        ));
    }

    /**
     * Formulaire d'ajout d'une espèce
     *
     * @Route("/espece/ajouter/{page}", name="espece_ajouter")
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request, int $page = 1)
    {
        $espece = new Espece();

        $form = $this->createForm(EspeceType::class, $espece);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $espece = $form->getData();
            $slug = $this->createSlug($request->request->all()["espece"]["nom"], 'Espece');
            
            $tag = new Tag();
            $tag->setNom(str_replace('_', ' ', $slug));
            
            $espece->setSlug($slug);
            $espece->setTag($tag);
            
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($tag);
            $manager->persist($espece);
            $manager->flush();

            return $this->redirectToRoute('espece_liste', array(
                'page' => $page
            ));
        }

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(
                $this->generateUrl('espece_liste', array(
                    'page' => $page
                )) => 'Espèces'
            ),
            'active' => "Ajout d'une espèce"
        );

        return $this->render('espece/ajouter.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'paths' => $paths
        ));
    }
}
