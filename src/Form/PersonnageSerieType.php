<?php
namespace App\Form;

use App\Entity\PersonnageSerie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\newType\RoleSerieType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Personnage;
use App\Repository\PersonnageRepository;
use App\Entity\Serie;
use App\Repository\SerieRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PersonnageSerieType extends AbstractType
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

        $builder->add('serie', EntityType::class, array(
            'class' => Serie::class,
            'choice_label' => function ($serie) {
                return $serie->getNom();
            },
            'query_builder' => function (SerieRepository $sr) {
                return $sr->createQueryBuilder('s')
                    ->orderBy('s.titre_original', 'ASC');
            },
            'disabled' => $options['disabled_serie']
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
            'data_class' => PersonnageSerie::class,
            'avec_principal' => true,
            'disabled_perso' => false,
            'disabled_serie' => false
        ));
    }
}
