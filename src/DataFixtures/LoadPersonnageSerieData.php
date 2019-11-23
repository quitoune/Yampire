<?php
namespace App\DataFixtures;

use App\Entity\PersonnageSerie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadPersonnageSerieData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/personnage_serie.json";
        $personnage_seriesArray = json_decode(file_get_contents($file), true);
        foreach ($personnage_seriesArray as $name => $objet) {
            
            $personnage_serie = new PersonnageSerie();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setSerie':
                    case 'setPersonnage':
                        $val = $this->getReference($val);
                        break;
                }
                $personnage_serie->{$key}($val);
            }
            
            $manager->persist($personnage_serie);
            $this->addReference($name, $personnage_serie);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadSerieData::class,
            LoadPersonnageData::class
        );
    }
}