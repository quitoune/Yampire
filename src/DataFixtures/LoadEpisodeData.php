<?php
namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadEpisodeData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/episode.json";
        $episodesArray = json_decode(file_get_contents($file), true);
        foreach ($episodesArray as $name => $objet) {
            
            $episode = new Episode();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setTag':
                    case 'setSerie':
                    case 'setSaison':
                        $val = $this->getReference($val);
                        $episode->{$key}($val);
                        break;
                    case 'setPremiereDiffusion':
                        $val = new \DateTime($val);
                        $episode->{$key}($val);
                        break;
                    default:
                        $episode->{$key}($val);
                        break;
                }
            }
            
            $manager->persist($episode);
            $this->addReference($name, $episode);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadTagData::class,
            LoadSerieData::class,
            LoadSaisonData::class
        );
    }
}