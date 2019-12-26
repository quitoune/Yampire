<?php

namespace App\Form\newType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Controller\AppController;

class RoleSerieType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array_flip(AppController::PERSONNAGE_SERIE_ROLES)
        ));
    }
    
    public function getParent()
    {
        return ChoiceType::class;
    }
}
