<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadPhotoTagData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "photo_tag.json";
        $photosArray = json_decode(file_get_contents($file), true);
        foreach ($photosArray as $name => $objet) {
            $photo = $this->getReference($name);
            
            foreach ($objet as $val) {
                $tag = $this->getReference($val);
                $photo->addTag($tag);
            }
            
            $manager->persist($photo);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadPhotoData::class,
            LoadTagData::class
        );
    }
}