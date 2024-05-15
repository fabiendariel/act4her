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


class QuestionnairePatientType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $date_options = [
            'widget' => 'single_text',
            'html5'   => true,
            'required'=> false
        ];
        $date_options_required = [
            'widget' => 'single_text',
            'html5'   => true,
            'required'=>true
        ];

        //$tab_reponses_enreg = $this->getOption("tab_reponses_enreg")?$this->getOption("tab_reponses_enreg"):array();
        $liste_choix_accepte = array('A'=>'1');

        $builder
          ->add('nom',           TextType::class, ['attr'   => ['maxlength' => '200']])
          ->add('prenom',        TextType::class, ['attr'   => ['maxlength' => '200']])
          ->add('adresse',       TextType::class, ['attr'   => ['maxlength' => '200']])
          ->add('cp',            TextType::class, ['attr'     => [
            'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
            'pattern' => '\d{5}',
            'maxlength' => '5'
          ]])
          ->add('ville',         TextType::class, ['attr'   => ['maxlength' => '200']])
          ->add('tel',           TextType::class, ['attr'     => [
            'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
            'maxlength' => '10',
            'pattern' => '0[1-9]\d{8}',
            'placeholder' => '0000000000'],'required'=>false])
          ->add('fax',           TextType::class, ['attr'     => [
            'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
            'maxlength' => '10',
            'pattern' => '0[1-9]\d{8}',
            'placeholder' => '0000000000'],'required'=>false])
          /*->add('specialite',           ChoiceType::class, array(
            'expanded' => false,
            'multiple' => false,
            'choices' => $liste_choix_accepte,
            'label_attr' => ['class' => 'radio-inline']
          ))*/

          ->add('traitant',              CheckboxType::class, ['required'=>false])
          ->add('nom_traitant',           TextType::class, ['attr'   => ['maxlength' => '200'],'required'=>false])
          ->add('prenom_traitant',        TextType::class, ['attr'   => ['maxlength' => '200'],'required'=>false])
          ->add('adresse_traitant',       TextType::class, ['attr'   => ['maxlength' => '200'],'required'=>false])
          ->add('cp_traitant',            TextType::class, ['attr'     => [
              'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
            'pattern' => '\d{5}',
            'maxlength' => '5'
          ],'required'=>false])
          ->add('ville_traitant',         TextType::class, ['attr'   => ['maxlength' => '200'],'required'=>false])
          ->add('tel_traitant',           TextType::class, ['attr'     => [
              'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
              'maxlength' => '10',
              'pattern' => '0[1-9]\d{8}',
              'placeholder' => '0000000000'],'required'=>false])
            ->add('fax_traitant',           TextType::class, ['attr'     => [
                'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
                'maxlength' => '10',
                'pattern' => '0[1-9]\d{8}',
                'placeholder' => '0000000000'],'required'=>false])
            ->add('email_traitant',                 EmailType::class, ['attr'   => ['maxlength' => '255'],'required'=>false])

          ->add('email_patient',         EmailType::class, ['attr'   => ['maxlength' => '200']])
          ->add('nom_patient',           TextType::class, ['attr'   => ['maxlength' => '200']])
          ->add('prenom_patient',        TextType::class, ['attr'   => ['maxlength' => '200']])
          ->add('adresse_patient',       TextType::class, ['attr'   => ['maxlength' => '200']])
          ->add('cp_patient',            TextType::class, ['attr'     => [
            'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
            'pattern' => '\d{5}',
            'maxlength' => '5'
          ]])
          ->add('ville_patient',         TextType::class, ['attr'   => ['maxlength' => '200']])
          ->add('mobile_patient',        TextType::class, ['attr'     => [
            'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
            'maxlength' => '10',
            'pattern' => '0[1-9]\d{8}',
            'placeholder' => '0600000000']])
          ->add('naissance_patient',     DateType::class, $date_options_required)



          ->add('code_validation',       TextType::class, ['attr'   => ['maxlength' => '200']])
          ->add('cgv',                   CheckboxType::class)
          ->add('courrier',              CheckboxType::class, ['required'=>false])
          ->add('collecte_informatique', CheckboxType::class)


            //Programme spécéfique
            ->add('pharmacie',      TextType::class, ['required'=>false])
            ->add('nom_infirmier', TextType::class, ['required'=>false])
            ->add('tel_infirmier',           TextType::class, ['attr'     => [
                'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
                'maxlength' => '10',
                'pattern' => '0[1-9]\d{8}',
                'placeholder' => '0000000000'],'required'=>false])
            ->add('dosage',              TextType::class, ['required'=>false])
            ->add('traitement',    TextType::class, ['required'=>false])
            ->add('traitement_associe',    TextType::class, ['required'=>false])
            ->add('date_traitement',     DateType::class, $date_options)
            ->add('initiation', ChoiceType::class, array(
                'required'=> false,
                'expanded' => false,
                'multiple' => false,
                'placeholder'   => '---',
                'choices' => array(
                    'Oui' => 1,
                    'Non' => 0,
                ),
                'label_attr' => ['class' => 'radio-inline']
            ))
            ->add('alerte3',          CheckboxType::class, ['required'=>false])
            ->add('alerteEquipe',     CheckboxType::class, ['required'=>false])
            ->add('alerteMail',       TextType::class, ['required'=>false])


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
            'data_class'         => null,
            'csrf_protection'    => true,
            'cascade_validation' => true
        ]);
    }
}
