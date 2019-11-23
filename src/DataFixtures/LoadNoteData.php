<?php
namespace App\DataFixtures;

use App\Entity\Note;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadNoteData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "resources/note.json";
        $notesArray = json_decode(file_get_contents($file), true);
        foreach ($notesArray as $name => $objet) {
            
            $note = new Note();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setUtilisateur':
                    case 'setActeur':
                    case 'setPersonnage':
                    case 'setEpisode':
                        $val = $this->getReference($val);
                        break;
                    case 'setDateCreation':
                        $val = new \DateTime($val);
                        break;
                }
                $note->{$key}($val);
            }
            
            $manager->persist($note);
            $this->addReference($name, $note);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadUtilisateurData::class,
            LoadActeurData::class,
            LoadPersonnageData::class,
            LoadEpisodeData::class
        );
    }
}