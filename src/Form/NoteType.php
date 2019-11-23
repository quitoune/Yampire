<?php

namespace App\Form;

use App\Entity\Note;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Acteur;
use App\Repository\ActeurRepository;
use App\Entity\Personnage;
use App\Repository\PersonnageRepository;
use App\Entity\Episode;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contenu', TextareaType::class, array(
            'label' => 'Contenu'
        ));
        
        if ($options['avec_acteur']) {
            $builder->add('acteur', EntityType::class, array(
                'class' => Acteur::class,
                'choice_label' => function ($acteur) {
                return $acteur->getPrenom() . ' ' . $acteur->getNom();
                },
                'query_builder' => function (ActeurRepository $ar) {
                return $ar->createQueryBuilder('a')
                ->orderBy('a.nom ASC, a.prenom', 'ASC');
                },
                'choice_label' => function (Acteur $acteur) {
                return $acteur->getPrenom() . ' ' . $acteur->getNom();
                },
                'required' => false,
                'disabled' => $options['disabled_acteur']
                ));
        }
        
        if ($options['avec_perso']) {
            $builder->add('personnage', EntityType::class, array(
                'class' => Personnage::class,
                'choice_label' => function ($personnage) {
                return $personnage->getPrenom() . ' ' . $personnage->getNom();
                },
                'query_builder' => function (PersonnageRepository $pr) {
                return $pr->createQueryBuilder('p')
                ->orderBy('p.nom ASC, p.prenom', 'ASC');
                },
                'choice_label' => function (Personnage $personnage) {
                return ($personnage->getPrenomUsage() ? $personnage->getPrenomUsage() : $personnage->getPrenom()) . ' ' . $personnage->getNom();
                },
                'required' => false,
                'disabled' => $options['disabled_perso']
                ));
        }
        
        if ($options['avec_episode']) {
            $builder->add('episode', EntityType::class, array(
                'class' => Episode::class,
                'choice_label' => 'titre',
                'required' => false,
                'group_by' => function (Episode $episode) {
                return $episode->getSerie()
                ->getNom() . ' - Saison ' . $episode->getSaison()
                ->getNumeroSaison();
                },
                'disabled' => $options['disabled_episode']
                ));
        }
        
        $builder->add('save', SubmitType::class, array(
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
            'data_class' => Note::class,
            'avec_perso' => false,
            'avec_acteur' => false,
            'avec_episode' => false,
            'disabled_perso' => false,
            'disabled_acteur' => false,
            'disabled_episode' => false
        ));
    }
}
