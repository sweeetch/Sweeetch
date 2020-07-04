<?php

namespace App\Form;

use App\Entity\Studies;
// use App\Form\SessionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class StudiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $titleConstraints = [
            // new NotBlank([
            //     'message' => 'Veuillez entrer un titre, svp',
            // ]),
            new Length([
                'min' => '2',
                'max' => '50',
                'minMessage' => "{{ limit }} caractères minimum",
                'maxMessage' => "{{ limit }} caractères maximum"
            ]),
            new Regex([
                'pattern' => "/[a-zA-Z0-9 !.,_-]+/",
                'message' => "Entrez un nom valide svp"
            ]),
        ];

        $videoConstraints = [
            new Regex([
                'pattern' => "/([a-zA-Z0-9_-]){11}/",
                'message' => "Entrez une url Youtube valide svp"
            ]),
        ];

        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'constraints' => $titleConstraints
            ])
            ->add('domain', ChoiceType::class, [
                'choices' => [
                    'Grande distribution' => 'Grande distribution',
                    'Vente & Commerce' => 'Vente & Commerce',
                    'Restauration' => 'Restauration',
                    'Artisanat' => 'Artisanat',
                    'Marketing & Communication' => 'Marketing & Communication',
                    'Assistanat & secrétariat' => 'Assistanat & secrétariat',
                    'Immobilier' => 'Immobilier',
                ],
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'attending_'.strtolower($key)];
                },
            ])
            ->add('video', TextType::class, [
                'constraints' => $videoConstraints,
                'required' => false
            ])
            ->add('title1', TextType::class, [
                'label' => '',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => '',
                'required' => false,
            ])
            ->add('title2', TextType::class, [
                'label' => '',
                'required' => false,
            ])
            ->add('description2', TextareaType::class, [
                'label' => '',
                'required' => false,
            ])
            ->add('title3', TextType::class, [
                'label' => '',
                'required' => false,
            ])
            ->add('description3', TextareaType::class, [
                'label' => '',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Studies::class,
        ]);
    }
}
