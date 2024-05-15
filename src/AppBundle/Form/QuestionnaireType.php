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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use AppBundle\Repository\TitreComplementRepository;

class QuestionnaireType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $date_options = [
            'widget' => 'single_text',
            'attr'   => [
                'date-format'   => 'dd/mm/yyyy',
            ]
        ];

        $builder
            ->add('rpps',            TextType::class, ['attr'   => ['maxlength' => '200']])
            ->add('nom',             TextType::class, ['attr'   => ['maxlength' => '200']])
            ->add('prenom',          TextType::class, ['attr'   => ['maxlength' => '200']])
            ->add('email',           EmailType::class, ['attr'   => ['maxlength' => '255']])
            ->add('specialite',      TextType::class, ['attr'   => ['maxlength' => '200'],'required' => false])
            ->add('adresse',         TextType::class, ['attr'   => ['maxlength' => '255'],'required' => false])
            ->add('tel',             TextType::class, [

                'attr'     => [
                    'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
                    'maxlength' => '10',
                    'pattern' => '0[1-9]\d{8}',
                    'placeholder' => '0000000000'
            ]])
            ->add('fax',             TextType::class, [

              'attr'     => [
                'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
                'maxlength' => '10',

                'pattern' => '0[1-9]\d{8}',
                'placeholder' => '0000000000'
              ],'required' => false])
            ->add('cp',            TextType::class, [
                'attr'     => [
                    'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
                    'maxlength' => '5'
                 ],'required' => false])
            ->add('ville',         TextType::class, ['attr'   => ['maxlength' => '200'],'required' => false])

            //->add('save',          SubmitType::class);
        ;


    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => null,
            'csrf_protection'    => true,
            'cascade_validation' => true
        ]);
    }
}
