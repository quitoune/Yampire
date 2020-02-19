<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Personnage;
use App\Repository\PersonnageRepository;
use App\Entity\Episode;
use App\Repository\EpisodeRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EpisodePersonnageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('personnage', EntityType::class, array(
            'class' => Personnage::class,
            'query_builder' => function (PersonnageRepository $pr) {
            return $pr->createQueryBuilder('p')
            ->orderBy('p.nom ASC, p.prenom', 'ASC');
            },
            'choice_label' => function (Personnage $personnage) {
            return ($personnage->getPrenomUsage() ? $personnage->getPrenomUsage() : $personnage->getPrenom()) . ' ' . $personnage->getNom();
            },
            'required' => false,
            'multiple' => true,
            'disabled' => $options['disabled_perso']
            ))
            ->add('episode', EntityType::class, array(
                'class' => Episode::class,
                'choice_label' => ($options['session']->get('user')['vo'] ? 'titreOriginal' : 'titre'),
                'query_builder' => function (EpisodeRepository $er) {
                return $er->createQueryBuilder('e')
                ->orderBy('e.numero_episode', 'ASC');
                },
                'group_by' => function (Episode $episode) {
                    return 'Saison ' . $episode->getSaison()->getNumeroSaison();
                },
                'disabled' => $options['disabled_episode']
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
            'update' => false,
            'disabled_perso' => false,
            'disabled_episode' => false
        ));
        
        $resolver->setRequired('session');
    }
}
