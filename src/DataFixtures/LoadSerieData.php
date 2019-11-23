<?php
namespace App\DataFixtures;

use App\Entity\Serie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LoadSerieData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/serie.json";
        $seriesArray = json_decode(file_get_contents($file), true);
        foreach ($seriesArray as $name => $objet) {
            
            $serie = new Serie();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setPhoto':
                        $val = $this->getReference($val);
                        break;
                }
                $serie->{$key}($val);
            }
            $manager->persist($serie);
            $this->addReference($name, $serie);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadPhotoData::class
        );
    }
}