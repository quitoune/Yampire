<?php

namespace App\Form\newType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Controller\QuestionController;

class TypeQuestionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array_flip(QuestionController::TYPE_QUESTION)
        ));
    }
    
    public function getParent()
    {
        return ChoiceType::class;
    }
}
