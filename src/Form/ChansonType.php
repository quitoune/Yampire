<?php

namespace App\Form;

use App\Entity\Chanson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Episode;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ChansonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('titre')
        ->add('interprete', TextType::class, array(
            'label' => 'Interprète'
        ))
        ->add('evenement', TextareaType::class, array(
            'label' => 'Evénement',
            'required' => false
        ))
        ->
        add('episode', EntityType::class, array(
            'class' => Episode::class,
            'disabled' => $options['disabled_episode'],
//             'choice_label' => ($options['session']->get('user')['episode_vo'] ? 'titreOriginal' : 'titre'),
            'choice_label' => function (Episode $episode) {
            return $episode->getTitre() . ' (' . $episode->getTitreOriginal() . ')';
            },
            'group_by' => function (Episode $episode) {
            return 'Saison ' . $episode->getSaison()->getNumeroSaison();
            },
            'choices' => $options['choices_episodes']
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
            'data_class' => Chanson::class,
            'choices_episodes' => array(),
            'disabled_episode' => false,
            'update' => false
        ));
        
        $resolver->setRequired('session');
    }
}
