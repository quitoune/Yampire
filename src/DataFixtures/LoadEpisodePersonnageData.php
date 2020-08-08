<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadEpisodePersonnageData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "episode_personnage.json";
        $episodesArray = json_decode(file_get_contents($file), true);
        foreach ($episodesArray as $name => $objet) {
            $episode = $this->getReference($name);
            
            foreach ($objet as $val) {
                $personnage = $this->getReference($val);
                $episode->addPersonnage($personnage);
            }
            
            $manager->persist($episode);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadEpisodeData::class,
            LoadPersonnageData::class
        );
    }
}