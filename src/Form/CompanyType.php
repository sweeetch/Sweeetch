<?php

namespace App\Form;

use App\Form\UserType;
use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('companyName', TextType::class, [
                'constraints' => [ 
                    new NotBlank(['message' => "Champ requis"]), 
                    new Length([
                        'min' => '2',
                        'max' => '30',
                        'minMessage' => "{{ limit }} caractères minimum",
                        'maxMessage' => "{{ limit }} caractères maximum"
                    ]),
                    new Regex([
                    'pattern' => "/[a-zA-Z0-9- ]/",
                    'message' => "Entrez un nom valide svp"
                    ])
                ],
            ])
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('address', TextType::class)
            ->add('zipCode', TextType::class, [
                'constraints' => [ 
                    new NotBlank(['message' => "Champ requis"]), 
                    new Regex([
                    'pattern' => "/^\d{5}(?:[-\s]\d{4})?$/",
                    'message' => "Entrez un code postal valide svp"
                    ])
                ],
            ])
            ->add('city', TextType::class)
            ->add('telNumber', TextType::class)
            ->add('siret', TextType::class)
            ->add('user', UserType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
