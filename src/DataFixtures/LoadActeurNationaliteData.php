<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadActeurNationaliteData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/acteur_nationalite.json";
        $acteursArray = json_decode(file_get_contents($file), true);
        foreach ($acteursArray as $name => $objet) {
            $acteur = $this->getReference($name);
            
            foreach ($objet as $val) {
                $nationalite = $this->getReference($val);
                $acteur->addNationalite($nationalite);
            }
            
            $manager->persist($acteur);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadActeurData::class,
            LoadNationaliteData::class
        );
    }
}