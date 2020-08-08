<?php
namespace App\DataFixtures;

use App\Entity\Personnage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadPersonnageData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "personnage.json";
        $personnagesArray = json_decode(file_get_contents($file), true);
        foreach ($personnagesArray as $name => $objet) {
            
            $personnage = new Personnage();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setTag':
                    case 'setPhoto':
                    case 'setEspece':
                        $val = $this->getReference($val);
                        $personnage->{$key}($val);
                        break;
                    case 'setDateNaissance':
                        $val = new \DateTime($val);
                        $personnage->{$key}($val);
                        break;
                    default:
                        $personnage->{$key}($val);
                        break;
                }
            }
            
            $manager->persist($personnage);
            $this->addReference($name, $personnage);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadTagData::class,
            LoadPhotoData::class,
            LoadEspeceData::class
        );
    }
}