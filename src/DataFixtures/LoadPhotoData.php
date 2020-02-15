<?php
namespace App\DataFixtures;

use App\Entity\Photo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LoadPhotoData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/photo.json";
        $photosArray = json_decode(file_get_contents($file), true);

        foreach ($photosArray as $name => $objet) {
            $photo = new Photo();
            foreach ($objet as $key => $val) {
                switch ($key){
                    case 'addTag':
                        foreach($val as $value){
                            $value = $this->getReference($value);
                            $photo->{$key}($value);
                        }
                        break;
                    default:
                        $photo->{$key}($val);
                        break;
                }
            }
            $manager->persist($photo);
            $this->addReference($name, $photo);
        }
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadTagData::class
        );
    }
}