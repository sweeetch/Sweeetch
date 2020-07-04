<?php

namespace App\Form;

use App\Entity\StudentCard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class StudentCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $card = $options['data'] ?? null;
        $isEdit = $card && $card->getId(); 

        $imageConstraints = [
            // new Image([
            //     'maxSize' => '5M'
            // ])
            new NotBlank([
                'message' => 'Veuillez uploader un fichier, svp'
            ]),
            new File([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/*',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'text/plain'
                ],
                'mimeTypesMessage' => 'Document invalide',
                'maxSizeMessage' => 'poids max : 5M',
            ])
        ];
        $builder
            ->add('file', FileType::class, [
                'mapped' => false,
                'constraints' => $imageConstraints
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StudentCard::class,
        ]);
    }
}
