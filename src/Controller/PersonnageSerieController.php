<?php
namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Serie;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\PersonnageSerie;
use App\Form\PersonnageSerieType;

class PersonnageSerieController extends AppController
{

    /**
     *
     * @Route("/personnage/serie", name="personnage_serie")
     */
    public function index()
    {
        return $this->render('personnage_serie/index.html.twig', [
            'controller_name' => 'PersonnageSerieController'
        ]);
    }

    /**
     * Ajouter une connexion entre un personnage et une série
     *
     * @Route("/personnage_serie/ajax_ajouter/{slug}", name="ajax_ajouter_personnage_serie")
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param Serie $serie
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAjouterDepuisSerie(Request $request, Serie $serie)
    {
        $personnage_serie = new PersonnageSerie();

        $form = $this->createForm(PersonnageSerieType::class, $personnage_serie, array(
            'disabled_serie' => true
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personnage_serie = $form->getData();

            $repo = $this->getDoctrine()->getRepository(PersonnageSerie::class);
            $element = $repo->findOneBy(array(
                'serie' => $serie->getId(),
                'personnage' => $personnage_serie->getPersonnage()
                    ->getId()
            ));

            if (is_null($element)) {
                $personnage_serie->setSerie($serie);
                $manager = $this->getDoctrine()->getManager();

                $manager->persist($personnage_serie);
                $manager->flush();
            }

            return $this->json(array(
                'statut' => true
            ));
        }

        return $this->render('personnage_serie/ajax_ajouter_depuis_serie.html.twig', array(
            'form' => $form->createView(),
            'serie' => $serie
        ));
    }

    /**
     * Supprimer une connexion entre un personnage et une série
     *
     * @Route("/personnage_serie/ajax_supprimer/{slug}", name="ajax_supprimer_personnage_serie")
     * @IsGranted("ROLE_UTILISATEUR")
     *
     * @param Request $request
     * @param Serie $serie
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxSupprimerDepuisSerie(Request $request, Serie $serie)
    {
        return $this->render('personnage_serie/ajax_supprimer_depuis_serie.html.twig', array(
            'serie' => $serie
        ));
    }
}
