<?php

namespace App\Form;

use App\Entity\Episode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Saison;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EpisodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('titre')
        ->add('titre_original', TextType::class, array(
            'label' => 'Titre Original'
        ))
        ->add('description', TextareaType::class, array(
            'required' => false
        ))
        ->add('numero_episode', IntegerType::class, array(
            'label' => 'N° de l\'épisode'
        ))
        ->add('numero_production', IntegerType::class, array(
            'label' => 'N° de production'
        ))
        ->add('premiere_diffusion', BirthdayType::class, array(
            'label' => 'Première diffusion',
            'required' => false,
            'widget' => 'choice',
            'format' => 'ddMMyyyy'
        ))
        ->add('duree', IntegerType::class, array(
            'label' => 'Durée'
        ))
        ->add('saison', EntityType::class, array(
            'class' => Saison::class,
            'choice_label' => function ($saison) {
            return 'Saison ' . $saison->getNumeroSaison();
            },
            'choices' => $options['choices_saison']
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
            'data_class' => Episode::class,
            'choices_saison' => array(),
            'update' => false
        ));
    }
}
