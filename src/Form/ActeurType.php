<?php

namespace App\Form;

use App\Entity\Acteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use App\Form\newType\SexeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ActeurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('prenom', TextType::class, array(
            'label' => 'Prénom'
        ))
        ->add('nom')
        ->add('date_naissance', BirthdayType::class, array(
            'label' => 'Date de naissance',
            'widget' => 'choice',
            'format' => 'ddMMyyyy',
            'required' => false
        ))
        ->add('sexe', SexeType::class)
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
            'data_class' => Acteur::class,
            'feminin' => false,
            'allow_extra_fields' => true,
            'update' => false
        ));
    }
}
