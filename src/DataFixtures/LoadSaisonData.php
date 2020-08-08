<?php
namespace App\DataFixtures;

use App\Entity\Saison;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LoadSaisonData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "saison.json";
        $saisonsArray = json_decode(file_get_contents($file), true);
        foreach ($saisonsArray as $name => $objet) {
            
            $saison = new Saison();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setTag':
                    case 'setSerie':
                    case 'setPhoto':
                        $val = $this->getReference($val);
                        break;
                }
                $saison->{$key}($val);
            }
            $manager->persist($saison);
            $this->addReference($name, $saison);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadTagData::class,
            LoadPhotoData::class,
            LoadSerieData::class
        );
    }
}