<?php

namespace App\Form;

use App\Entity\Personnage;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use App\Form\newType\SexeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Espece;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;

class PersonnageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('prenom')
        ->add('prenom_usage', TextType::class, array(
            'label' => 'Prénom d\'usage',
            'required' => false
        ))
        ->add('nom')
        ->add('date_naissance', BirthdayType::class, array(
            'label' => 'Date de naissance',
            'required' => false,
            'widget' => 'choice',
            'format' => 'ddMMyyyy'
        ))
        ->add('sexe', SexeType::class)
        ->add('espece', EntityType::class, array(
            'class' => Espece::class,
            'choice_label' => 'nom',
            'label' => 'Espèce'
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
            'data_class' => Personnage::class,
            'allow_extra_fields' => true,
            'update' => false
        ));
    }
}
