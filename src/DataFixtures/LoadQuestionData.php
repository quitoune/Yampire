<?php
namespace App\DataFixtures;

use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadQuestionData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "question.json";
        $questionsArray = json_decode(file_get_contents($file), true);
        foreach ($questionsArray as $name => $objet) {
            
            $question = new Question();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setUtilisateur':
                    case 'setCitation':
                        $val = $this->getReference($val);
                        break;
                    case 'setDateCreation':
                        $val = new \DateTime($val);
                        break;
                }
                $question->{$key}($val);
            }
            
            $question->setDateCreation(new \DateTime());
            
            $manager->persist($question);
            $this->addReference($name, $question);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadCitationData::class,
            LoadUtilisateurData::class
        );
    }
}