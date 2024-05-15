<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use AppBundle\Repository\TitreComplementRepository;

class ProfessionnelType extends AbstractType
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
            ->add('nom',             TextType::class, ['attr'   => ['maxlength' => '200'], 'required' => false])
            ->add('prenom',          TextType::class, ['attr'   => ['maxlength' => '200'], 'required' => false])
            ->add('dateNaissance',    DateType::class, $date_options)
            ->add('heure',    TimeType::class, [  'minutes' => array(
                0,
                30
            )])
            ->add('formationInitiale',    DateType::class, $date_options)
            ->add('titre',             EntityType::class, [
                'class'         => 'AppBundle:Titre',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('statutProfessionnel',             EntityType::class, [
                'class'         => 'AppBundle:StatutProfessionnel',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('titreComplement',             EntityType::class, [
                'class'         => 'AppBundle:TitreComplement',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('specialite',             EntityType::class, [
                'class'         => 'AppBundle:Specialite',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('role',             EntityType::class, [
                'class'         => 'AppBundle:Role',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('competence',             EntityType::class, [
                'class'         => 'AppBundle:Competence',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('statut',             EntityType::class, [
                'class'         => 'AppBundle:Statut',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('habilitation',             EntityType::class, [
                'class'         => 'AppBundle:Habilitation',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('badge',             EntityType::class, [
                'class'         => 'AppBundle:Badge',
                'choice_label'  => 'label',
                'placeholder'   => '---',
                'required' => false
            ])
            ->add('prerequis', ChoiceType::class, array(
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
            ->add('participation', ChoiceType::class, array(
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
            ->add('convocation', ChoiceType::class, array(
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

            ->add('membre', ChoiceType::class, array(
                'expanded' => false,
                'multiple' => false,
                'placeholder'   => '---',
                'required' => false,
                'choices' => array(
                    'Oui en cours' => 1,
                    'Non' => 0,
                ),
                'label_attr' => ['class' => 'radio-inline']
            ))
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
            ->add('formationEffectue', ChoiceType::class, array(
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
            ->add('lundiMatin', CheckboxType::class, ['required' => false])
            ->add('lundiAprem', CheckboxType::class, ['required' => false])
            ->add('mardiMatin', CheckboxType::class, ['required' => false])
            ->add('mardiAprem', CheckboxType::class, ['required' => false])
            ->add('mercrediMatin', CheckboxType::class, ['required' => false])
            ->add('mercrediAprem', CheckboxType::class, ['required' => false])
            ->add('jeudiMatin', CheckboxType::class, ['required' => false])
            ->add('jeudiAprem', CheckboxType::class, ['required' => false])
            ->add('vendrediMatin', CheckboxType::class, ['required' => false])
            ->add('vendrediAprem', CheckboxType::class, ['required' => false])
            ->add('samediMatin', CheckboxType::class, ['required' => false])
            ->add('samediAprem', CheckboxType::class, ['required' => false])
            ->add('dimancheAprem', CheckboxType::class, ['required' => false])
            ->add('dimancheMatin', CheckboxType::class, ['required' => false])
            ->add('picpus', CheckboxType::class, ['required' => false])
            ->add('distance', CheckboxType::class, ['required' => false])
            ->add('rpps',          TextType::class, [
                'required' => false,
                'attr'     => [
                    'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
                    'maxlength' => '20'
                ]])
            ->add('email',          EmailType::class, ['attr'   => ['maxlength' => '255'], 'required' => false])
            ->add('mobile',          TextType::class, [
                'required' => false,
                'attr'     => [
                    'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
                    'maxlength' => '10'
            ]])
            ->add('codePostal',          TextType::class, [
                'required' => false,
                'attr'     => [
                    'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
                    'maxlength' => '5'
                 ]])
            ->add('save',          SubmitType::class);
        ;


    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'AppBundle\Entity\Professionnel',
            'cascade_validation' => true
        ]);
    }
}
