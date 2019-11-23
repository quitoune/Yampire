<?php
namespace App\DataFixtures;

use App\Entity\Espece;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadEspeceData extends Fixture implements ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/espece.json";
        $especesArray = json_decode(file_get_contents($file), true);
        foreach ($especesArray as $name => $objet) {

            $espece = new Espece();

            foreach ($objet as $key => $val) {
                $espece->{$key}($val);
            }
            $manager->persist($espece);
            $this->addReference($name, $espece);
        }

        $manager->flush();
    }
}