<?php
namespace App\DataFixtures;

use App\Entity\Acteur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadActeurData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "acteur.json";
        $acteursArray = json_decode(file_get_contents($file), true);
        
        foreach ($acteursArray as $name => $objet) {
            $acteur = new Acteur();
            
            foreach ($objet as $key => $val) {
                switch ($key){
                    case 'setPhoto':
                    case 'setTag':
                        $val = $this->getReference($val);
                        $acteur->{$key}($val);
                        break;
                    case 'addNationalite':
                        foreach($val as $value){
                            $val = $this->getReference($value);
                            $acteur->{$key}($val);
                        }
                        break;
                    case 'setDateNaissance':
                        $val = new \DateTime($val);
                        $acteur->{$key}($val);
                        break;
                    default:
                        $acteur->{$key}($val);
                        break;
                }
            }
            $manager->persist($acteur);
            $this->addReference($name, $acteur);
        }
        $manager->flush();
    }
    public function getDependencies()
    {
        return array(
            LoadTagData::class,
            LoadNationaliteData::class,
            LoadPhotoData::class
        );
    }
}