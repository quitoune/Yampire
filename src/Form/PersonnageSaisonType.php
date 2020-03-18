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
        if ($options['choices']) {
            $builder->add('personnage', EntityType::class, array(
                'class' => Personnage::class,
                'choices' => $options['choices'],
                'choice_label' => function (Personnage $personnage) {
                    return ($personnage->getPrenomUsage() ? $personnage->getPrenomUsage() : $personnage->getPrenom()) . ' ' . $personnage->getNom();
                },
                'multiple' => true,
                'disabled' => $options['disabled_perso']
            ));
        } else {
            $builder->add('personnage', EntityType::class, array(
                'class' => Personnage::class,
                'query_builder' => function (PersonnageRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->orderBy('p.nom ASC, p.prenom', 'ASC');
                },
                'choice_label' => function (Personnage $personnage) {
                    return ($personnage->getPrenomUsage() ? $personnage->getPrenomUsage() : $personnage->getPrenom()) . ' ' . $personnage->getNom();
                },
                'disabled' => $options['disabled_perso']
            ));
        }

        $builder->add('saison', EntityType::class, array(
            'class' => Saison::class,
            'choice_label' => function ($saison) {
                return $saison->getSerie()
                    ->getNom() . ' - Saison ' . $saison->getNumeroSaison();
            },
            'query_builder' => function (SaisonRepository $sr) {
                return $sr->createQueryBuilder('s')
                    ->orderBy('s.numero_saison', 'ASC');
            },
            'disabled' => $options['disabled_saison']
        ))
            ->add('save', SubmitType::class, array(
            'label' => $options['label_button'],
            'attr' => array(
                'class' => 'btn btn-dark'
            )
        ));

        if ($options['avec_principal']) {
            $builder->add('principal', RoleSerieType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'label_button' => 'Ajouter',
            'choices' => false,
            'data_class' => PersonnageSaison::class,
            'avec_principal' => true,
            'disabled_perso' => false,
            'disabled_saison' => false
        ));
    }
}
