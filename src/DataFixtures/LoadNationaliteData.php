<?php
namespace App\DataFixtures;

use App\Entity\Nationalite;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadNationaliteData extends Fixture implements ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/nationalite.json";
        $nationalitesArray = json_decode(file_get_contents($file), true);
        foreach ($nationalitesArray as $name => $objet) {
            $nationalite = new Nationalite();

            foreach ($objet as $key => $val) {
                $nationalite->{$key}($val);
            }
            $manager->persist($nationalite);
            $this->addReference($name, $nationalite);
        }
        $manager->flush();
    }
}