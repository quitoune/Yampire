<?php
namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUtilisateurData extends Fixture implements ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/utilisateur.json";
        $utilisateursArray = json_decode(file_get_contents($file), true);

        foreach ($utilisateursArray as $name => $objet) {
            $utilisateur = new Utilisateur();

            foreach ($objet as $key => $val) {

                if ($key == 'setPassword') {
                    $password = $this->encoder($utilisateur, $val);
                    $utilisateur->{$key}($password);
                } else {
                    $utilisateur->{$key}($val);
                }
            }
            $manager->persist($utilisateur);
            $this->addReference($name, $utilisateur);
        }
        $manager->flush();
    }

    private function encoder(Utilisateur $utilisateur, $val)
    {
        return $this->encoder->encodePassword($utilisateur, $val);
    }
}