<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\newType\YesNoType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'Les 2 mots de passe doivent correspondent.',
            'required' => true,
            'first_options' => array(
                'label' => 'Mot de Passe'
            ),
            'second_options' => array(
                'label' => 'Mot de passe (répétition)'
            )
        ))
        ->add('prenom', TextType::class, array(
            'label' => 'Prénom'
        ))
        ->add('nom')
        ->add('username', TextType::class, array(
            'label' => 'Pseudo'
        ))
        ->add('serie_vo', YesNoType::class, array(
            'label' => 'Titre original des séries'
        ))
        ->add('episode_vo', YesNoType::class, array(
            'label' => 'Titre original des épisodes'
        ))
        ->add('question_1', TextareaType::class, array(
            'label' => 'Question 1'
        ))
        ->add('reponse_1', TextType::class, array(
            'label' => 'Réponse 1'
        ))
        ->add('question_2', TextareaType::class, array(
            'label' => 'Question 2'
        ))
        ->add('reponse_2', TextType::class, array(
            'label' => 'Réponse 2'
        ))
        ->add('save', SubmitType::class, array(
            'label' => $options['label_submit'],
            'attr' => array(
                'class' => 'btn btn-dark'
            )
        ));
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Utilisateur::class,
            'label_submit' => 'Valider'
        ));
    }
}
