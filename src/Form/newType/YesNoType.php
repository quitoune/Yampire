<?php

namespace App\Form\newType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class YesNoType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                'Non' => 0,
                'Oui' => 1,
            ),
        ));
    }
    
    public function getParent()
    {
        return ChoiceType::class;
    }
}
