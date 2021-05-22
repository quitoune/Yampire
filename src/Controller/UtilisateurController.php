<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UtilisateurType;

class UtilisateurController extends AppController
{
    /**
     * Liste des utilisateurs
     *
     * @Route("/utilisateur/liste/{page}", name="utilisateur_liste")
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(Utilisateur::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max);
        $utilisateurs = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'utilisateur_liste',
            'route_params' => array()
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Utilisateurs'
        );

        return $this->render('utilisateur/index.html.twig', array(
            'utilisateurs' => $utilisateurs,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }
    
    /**
     *  Affichage du compte
     * 
     * @Route("/utilisateur/profil/{username}", name="utilisateur_profil")
     * @Security("is_granted('ROLE_UTILISATEUR')")
     * 
     * @param Utilisateur $utilisateur
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profil(Utilisateur $utilisateur)
    {
        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Mon compte'
        );
        return $this->render('utilisateur/profil.html.twig', array(
            'utilisateur' => $utilisateur,
            'paths' => $paths
        ));
    }
    
    /**
     *  Affichage du compte
     *
     * @Route("/utilisateur/ajax/profil/{username}/{page}", name="ajax_utilisateur_profil")
     * @Security("is_granted('ROLE_UTILISATEUR')")
     *
     * @param Utilisateur $utilisateur
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxProfil(Utilisateur $utilisateur, int $page = 1)
    {
        switch($page){
            case 1:
                return $this->render('utilisateur/ajax_profil_info.html.twig', array(
                'utilisateur' => $utilisateur
                ));
            case 2:
                return $this->render('utilisateur/ajax_profil_config.html.twig', array(
                'utilisateur' => $utilisateur
                ));
            case 3:
                return $this->render('utilisateur/ajax_profil_connexion.html.twig', array(
                'utilisateur' => $utilisateur
                ));
        }
    }

    /**
     *
     * @Route("/register", name="register")
     *
     * @param UserPasswordEncoderInterface $encoder
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function register(UserPasswordEncoderInterface $encoder, Request $request)
    {
        $utilisateur = new Utilisateur();

        $form = $this->createForm(UtilisateurType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $utilisateur = $form->getData();

            $utilisateur->setSalt($this->generateSalt());
            $encodePassword = $encoder->encodePassword($utilisateur, $utilisateur->getPassword());
            $utilisateur->setPassword($encodePassword);
            $utilisateur->setRoles(array(
                'ROLE_UTILISATEUR'
            ));

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($utilisateur);
            $manager->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('utilisateur/register.html.twig', array(
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
            'paths' => array(
                'home' => $this->homeURL()
            )
        ));
    }

    /**
     *
     * @Route("/lost_password", name="lost_password")
     *
     * @param UserPasswordEncoderInterface $encoder
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function lostPassword(UserPasswordEncoderInterface $encoder, Request $request)
    {
        $questions = null;
        $new_password = null;

        $all = $request->request->all();

        if (isset($all["get_username"])) {
            $utilisateur = $this->getDoctrine()
                ->getRepository(Utilisateur::class)
                ->findOneBy(array(
                'username' => $all["username"]
            ));
            
            $questions["username"] = $all["username"];
            $questions["question_1"] = $utilisateur->getQuestion1();
            $questions["question_2"] = $utilisateur->getQuestion2();
        }
        
        if(isset($all["check_questions"])){
            $utilisateur = $this->getDoctrine()
            ->getRepository(Utilisateur::class)
            ->findBy(array(
                'username' => $all["username"],
                'question_1' => $all["reponse_1"],
                'question_2' => $all["reponse_2"]
            ));
            
            if(!is_null($utilisateur)){
                $questions["username"] = $all["username"];
                $new_password = true;
            } else {
                
            }
        }
        
        if(isset($all[""])){
            
        }
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {

        // $utilisateur = $form->getData();

        // $utilisateur->setSalt($this->generateSalt());
        // $encodePassword = $encoder->encodePassword($utilisateur, $utilisateur->getPassword());
        // $utilisateur->setPassword($encodePassword);
        // $utilisateur->setRoles(array('ROLE_UTILISATEUR'));

        // $manager = $this->getDoctrine()->getManager();
        // $manager->persist($utilisateur);
        // $manager->flush();

        // return $this->redirectToRoute('index');
        // }

        return $this->render('utilisateur/lost_password.html.twig', array(
            'questions' => $questions,
            'new_password' => $new_password,
            'paths' => array(
                'home' => $this->homeURL()
            )
        ));
    }

    /**
     * Génération automatique de salt
     */
    public function generateSalt()
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }
}
