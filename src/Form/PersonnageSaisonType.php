<?php

namespace App\Form;

use App\Entity\PersonnageSaison;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\newType\RoleSerieType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Personnage;
use App\Repository\PersonnageRepository;
use App\Entity\Saison;
use App\Repository\SaisonRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PersonnageSaisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('principal', RoleSerieType::class)
        ->add('personnage', EntityType::class, array(
            'class' => Personnage::class,
            'query_builder' => function (PersonnageRepository $pr) {
            return $pr->createQueryBuilder('p')
            ->orderBy('p.nom ASC, p.prenom', 'ASC');
            },
            'choice_label' => function (Personnage $personnage) {
            return ($personnage->getPrenomUsage() ? $personnage->getPrenomUsage() : $personnage->getPrenom()) . ' ' . $personnage->getNom();
            },
            'disabled' => $options['disabled_perso']
            ))
            ->add('saison', EntityType::class, array(
                'class' => Saison::class,
                'choice_label' => function ($saison) {
                return $saison->getSerie()->getNom() . ' - Saison ' . $saison->getNumeroSaison();
                },
                'query_builder' => function (SaisonRepository $sr) {
                return $sr->createQueryBuilder('s')
                ->orderBy('s.numero_saison', 'ASC');
                },
                'disabled' => $options['disabled_saison']
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
            'data_class' => PersonnageSaison::class,
            'disabled_perso' => false,
            'disabled_saison' => false
        ));
    }
}
