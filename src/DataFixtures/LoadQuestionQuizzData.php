<?php
namespace App\DataFixtures;

use App\Entity\QuestionQuizz;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadQuestionQuizzData extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $file  = str_replace("\\", "/", $this->container->getParameter('resources_directory'));
        $file .= "question_quizz.json";
        $question_quizzsArray = json_decode(file_get_contents($file), true);
        foreach ($question_quizzsArray as $name => $objet) {
            
            $question_quizz = new QuestionQuizz();
            
            foreach ($objet as $key => $val) {
                switch($key){
                    case 'setQuestion':
                    case 'setQuizz':
                        $val = $this->getReference($val);
                        break;
                }
                $question_quizz->{$key}($val);
            }
            
            $manager->persist($question_quizz);
            $this->addReference($name, $question_quizz);
        }
        
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return array(
            LoadQuestionData::class,
            LoadQuizzData::class
        );
    }
}