<?php
namespace App\DataFixtures;

use App\Entity\Chanson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadChansonData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "chanson.json";
        $chansonsArray = json_decode(file_get_contents($file), true);
        foreach ($chansonsArray as $name => $objet) {
            
            $chanson = new Chanson();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setEpisode':
                        $val = $this->getReference($val);
                        break;
                }
                $chanson->{$key}($val);
            }
            
            $manager->persist($chanson);
            $this->addReference($name, $chanson);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadEpisodeData::class
        );
    }
}