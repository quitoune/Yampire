<?php

namespace App\Form;

use App\Entity\Espece;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EspeceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom')
        ->add('info_sup', TextareaType::class, array(
            'label' => 'Information supplémentaire',
            'required' => false
        ))
        ->add('pouvoirs', TextareaType::class, array(
            'required' => false
        ))
        ->add('faiblesses', TextareaType::class, array(
            'required' => false
        ))
        ->add('save', SubmitType::class, array(
            'label' => ($options['update'] ? 'Sauvegarder' : 'Ajouter'),
            'attr' => array(
                'class' => 'btn btn-dark'
            )
        ));
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Espece::class,
            'update' => false
        ));
    }
}
