<?php
namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTagData extends Fixture implements ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/tag.json";
        $tagsArray = json_decode(file_get_contents($file), true);
        foreach ($tagsArray as $name => $objet) {

            $tag = new Tag();

            foreach ($objet as $key => $val) {
                $tag->{$key}($val);
            }
            $manager->persist($tag);
            $this->addReference($name, $tag);
        }

        $manager->flush();
    }
}