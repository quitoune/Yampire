<?php
namespace App\DataFixtures;

use App\Entity\ActeurPersonnage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadActeurPersonnageData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "acteur_personnage.json";
        $acteur_personnagesArray = json_decode(file_get_contents($file), true);
        foreach ($acteur_personnagesArray as $name => $objet) {
            
            $acteur_personnage = new ActeurPersonnage();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setActeur':
                    case 'setPersonnage':
                        $val = $this->getReference($val);
                        break;
                }
                $acteur_personnage->{$key}($val);
            }
            
            $manager->persist($acteur_personnage);
            $this->addReference($name, $acteur_personnage);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadActeurData::class,
            LoadPersonnageData::class
        );
    }
}