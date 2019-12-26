<?php
namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\newType\TypeQuestionType;
use App\Form\newType\TypePropositionType;
use App\Form\newType\ReponseType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class QuestionType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type_question', TypeQuestionType::class)
            ->add('type_proposition', TypePropositionType::class)
            ->add('intitule', TextareaType::class)
            ->add('reponse', ReponseType::class)
            ->add('explication', TextareaType::class, array(
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
            'update' => false,
            'data_class' => Question::class
        ));
    }
}
