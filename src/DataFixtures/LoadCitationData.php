<?php
namespace App\DataFixtures;

use App\Entity\Citation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCitationData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "citation.json";
        $citationsArray = json_decode(file_get_contents($file), true);
        foreach ($citationsArray as $name => $objet) {
            
            $citation = new Citation();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setFromPersonnage':
                    case 'setToPersonnage1':
                    case 'setToPersonnage2':
                    case 'setUtilisateur':
                    case 'setEpisode':
                        $val = $this->getReference($val);
                        break;
                    case 'setDateCreation':
                        $val = new \DateTime($val);
                        break;
                }
                $citation->{$key}($val);
            }
            
            $citation->setDateCreation(new \DateTime());
            
            $manager->persist($citation);
            $this->addReference($name, $citation);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadPersonnageData::class,
            LoadUtilisateurData::class,
            LoadEpisodeData::class
        );
    }
}