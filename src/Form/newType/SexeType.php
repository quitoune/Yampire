<?php

namespace App\Form\newType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SexeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                'Masculin' => 0,
                'Féminin' => 1,
            ),
        ));
    }
    
    public function getParent()
    {
        return ChoiceType::class;
    }
}
