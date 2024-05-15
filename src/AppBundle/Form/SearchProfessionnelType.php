<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

use AppBundle\Repository\TitreComplementRepository;

class SearchProfessionnelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $date_options = [
            'widget' => 'single_text',
            'required' => false,
            'attr'   => [
                'date-format'   => 'dd/mm/yyyy',
            ]
        ];

        $builder
            ->add('nom',             TextType::class, [
                'required' => false,
                'attr'   => ['maxlength' => '200']])
            ->add('profil',     EntityType::class, [
                'class'         => 'AppBundle:Profil',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('titreComplement',             EntityType::class, [
                'class'         => 'AppBundle:TitreComplement',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false,
                'query_builder' => function (TitreComplementRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->where('t.autre = 0')
                        ->orderBy('t.ordre', 'ASC');
                }
            ])
            ->add('titreComplementAutres',             EntityType::class, [
                'class'         => 'AppBundle:TitreComplement',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false,
                'query_builder' => function (TitreComplementRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->where('t.autre = 1')
                        ->orderBy('t.ordre', 'ASC');
                }
            ])
            ->add('statut',     EntityType::class, [
                'class'         => 'AppBundle:Statut',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('statutProfessionnel',     EntityType::class, [
                'class'         => 'AppBundle:StatutProfessionnel',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('formationInitiale',    DateType::class, $date_options)
            ->add('heure',    TimeType::class, [  'minutes' => array(
                0,
                30
            )])
            ->add('disponibilite', ChoiceType::class, array(
                'expanded' => false,
                'multiple' => false,
                'placeholder'   => '---',
                'required' => false,
                'choices' => array(
                    'Oui' => 1,
                    'Non' => 0,
                ),
                'label_attr' => ['class' => 'radio-inline']
            ))
            ->add('dateDebut',   DateType::class, $date_options)
            ->add('dateFin',    DateType::class, $date_options)
            ->add('save', SubmitType::class);
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'csrf_protection' => false
        ));
    }
    
    
}
