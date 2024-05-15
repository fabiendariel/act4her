<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdministrationUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname',     TextType::class)
            ->add('lastname',      TextType::class)
            ->add('username',      TextType::class, ['attr' => ['autocomplete' => 'false']])
            ->add('email',         TextType::class, ['attr' => ['autocomplete' => 'false']])
            ->add('password',      PasswordType::class, ['attr' => ['autocomplete' => 'false']])
            ->add('urlAdobe',      TextType::class, ['attr' => ['autocomplete' => 'false']])
            ->add('urlIslider',    TextType::class, ['attr' => ['autocomplete' => 'false']])
            ->add('idOperation',   TextType::class, ['attr' => ['autocomplete' => 'false']])
            ->add('roles', ChoiceType::class, array(
                'choices' => array(
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                    'ROLE_USER' => 'ROLE_USER'
                ),
                'multiple' => true,
                'placeholder' => 'Choisir le role',
                'empty_data'  => null
            ))
        ;


    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'AppBundle\Entity\User',
            'cascade_validation' => true
        ]);
    }
}
