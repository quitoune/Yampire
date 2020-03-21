<?php
namespace App\Form;

use App\Entity\Citation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Personnage;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Entity\Episode;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CitationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('texte')
            ->add('from_personnage', EntityType::class, array(
            'class' => Personnage::class,
            'label' => 'Auteur',
            'disabled' => $options['disabled_personnage'],
            'choices' => $options['choices_personnages'],
            'choice_label' => function (Personnage $personnage) {
                return $personnage->getNomUsage();
            }
        ))
            ->add('to_personnage_1', EntityType::class, array(
            'class' => Personnage::class,
            'label' => 'Destinataire 1',
            'required' => false,
            'choices' => $options['choices_personnages'],
            'choice_label' => function (Personnage $personnage) {
                return $personnage->getNomUsage();
            }
        ))
            ->add('to_personnage_2', EntityType::class, array(
            'class' => Personnage::class,
            'label' => 'Destinataire 2',
            'choices' => $options['choices_personnages'],
            'choice_label' => function (Personnage $personnage) {
                return $personnage->getNomUsage();
            },
            'required' => false
        ))
            ->add('to_personnage', TextType::class, array(
            'label' => 'Destinataire (autre)',
            'required' => false
        ))
            ->add('episode', EntityType::class, array(
            'class' => Episode::class,
            'disabled' => $options['disabled_episode'],
//             'choice_label' => ($options['session']->get('user')['episode_vo'] ? 'titreOriginal' : 'titre'),
            'choice_label' => function (Episode $episode) {
                return $episode->getTitre() . ' (' . $episode->getTitreOriginal() . ')';
            },
            'group_by' => function (Episode $episode) {
                return $episode->getSerie()
                    ->getTitreCourt() . ' - Saison ' . $episode->getSaison()
                    ->getNumeroSaison();
            },
            'choices' => $options['choices_episodes']
        ))
            ->add('save', SubmitType::class, array(
            'label' => $options['update'],
            'attr' => array(
                'class' => 'btn btn-dark'
            )
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'update' => 'Ajouter',
            'allow_extra_fields' => true,
            'data_class' => Citation::class,
            'disabled_episode' => false,
            'disabled_personnage' => false,
            'choices_episodes' => array(),
            'choices_personnages' => array()
        ));

        $resolver->setRequired('session');
    }
}
