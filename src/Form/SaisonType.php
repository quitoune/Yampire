<?php

namespace App\Form;

use App\Entity\Saison;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Serie;
use App\Repository\SerieRepository;
use App\Entity\Photo;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SaisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('numero_saison', IntegerType::class, array(
            'label' => 'N° de saison'
        ))
        ->add('nombre_episode', IntegerType::class, array(
            'label' => 'Nombre d\'épisode'
        ))
        ->add('serie', EntityType::class, array(
            'class' => Serie::class,
            'choice_label' => 'nom',
            'label' => 'Série',
            'query_builder' => function (SerieRepository $sr) {
            return $sr->createQueryBuilder('s')
            ->orderBy('s.nom', 'ASC');
            }
            ))
            ->add('photo', EntityType::class, array(
                'class' => Photo::class,
                'choice_label' => 'nom',
                'required' => false
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
            'data_class' => Saison::class,
            'update' => false
        ));
    }
}
