<?php
namespace App\DataFixtures;

use App\Entity\Quizz;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadQuizzData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/quizz.json";
        $quizzsArray = json_decode(file_get_contents($file), true);
        foreach ($quizzsArray as $name => $objet) {
            
            $quizz = new Quizz();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setUtilisateur':
                        $val = $this->getReference($val);
                        break;
                    case 'setDateCreation':
                        $val = new \DateTime($val);
                        break;
                }
                $quizz->{$key}($val);
            }
            
            $manager->persist($quizz);
            $this->addReference($name, $quizz);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadUtilisateurData::class
        );
    }
}