<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Saison;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\PersonnageSaison;
use App\Form\PersonnageSaisonType;

class PersonnageSaisonController extends AppController
{
    /**
     * @Route("/personnage/saison", name="personnage_saison")
     */
    public function index()
    {
        return $this->render('personnage_saison/index.html.twig', [
            'controller_name' => 'PersonnageSaisonController',
        ]);
    }
    
    /**
     * Ajouter une connexion entre un personnage et une saison
     *
     * @Route("/personnage_saison/ajax_ajouter/{id}", name="ajax_ajouter_personnage_saison")
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_UTILISATEUR')")
     * 
     * @param Request $request
     * @param Saison $saison
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAjouterDepuisSaison(Request $request, Saison $saison){
        $personnage_saison = new PersonnageSaison();
        
        $form = $this->createForm(PersonnageSaisonType::class, $personnage_saison, array(
            'disabled_saison' => true
        ));
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $personnage_saison = $form->getData();
            $personnage_saison->setSaison($saison);
            $manager = $this->getDoctrine()->getManager();
            
            $manager->persist($personnage_saison);
            $manager->flush();
            
            return $this->json(array(
                'statut' => true
            ));
        }
        
        return $this->render('personnage_saison/ajax_ajouter_depuis_saison.html.twig', array(
            'form' => $form->createView(),
            'saison' => $saison
        ));
    }
}
