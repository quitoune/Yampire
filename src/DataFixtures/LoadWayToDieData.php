<?php
namespace App\DataFixtures;

use App\Entity\WayToDie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadWayToDieData extends Fixture implements ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/way_to_die.json";
        $way_to_diesArray = json_decode(file_get_contents($file), true);
        foreach ($way_to_diesArray as $name => $objet) {

            $way_to_die = new WayToDie();

            foreach ($objet as $key => $val) {
                $way_to_die->{$key}($val);
            }
            $manager->persist($way_to_die);
            $this->addReference($name, $way_to_die);
        }

        $manager->flush();
    }
}