<?php

namespace App\Form;

use App\Entity\Serie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\newType\YesNoType;

class SerieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom')
        ->add('nom_court', TextType::class, array(
            'label' => 'Nom court'
        ))
        ->add('nombre_saison', NumberType::class, array(
            'label' => 'Nombre de saisons'
        ))
        ->add('nombre_episode', NumberType::class, array(
            'label' => 'Nombre d\'épisodes'
        ))
        ->add('terminee', YesNoType::class, array(
            'label' => 'Terminée',
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
            'data_class' => Serie::class,
            'update' => false
        ));
    }
}
