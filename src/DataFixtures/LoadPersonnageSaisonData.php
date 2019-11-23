<?php
namespace App\DataFixtures;

use App\Entity\PersonnageSaison;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadPersonnageSaisonData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/personnage_saison.json";
        $personnage_saisonsArray = json_decode(file_get_contents($file), true);
        foreach ($personnage_saisonsArray as $name => $objet) {
            
            $personnage_saison = new PersonnageSaison();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setSaison':
                    case 'setPersonnage':
                        $val = $this->getReference($val);
                        break;
                }
                $personnage_saison->{$key}($val);
            }
            
            $manager->persist($personnage_saison);
            $this->addReference($name, $personnage_saison);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadSaisonData::class,
            LoadPersonnageData::class
        );
    }
}