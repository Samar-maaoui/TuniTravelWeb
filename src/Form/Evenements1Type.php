<?php

namespace App\Form;

use App\Entity\Evenements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\GreaterThan;

class Evenements1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom ',
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'Nom '
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                ]
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu ',
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'Lieu '
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le lieu est obligatoire.']),
                ]
            ])
            ->add('date_evenement', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'html5' => false, 
                'format' => 'dd/MM/yyyy', 
                'attr' => [
                    'class' => 'form-control custom-field datepicker',
                    'placeholder' => 'ex:20/01/2026',
                    'autocomplete' => 'off'
                ],
                'input' => 'datetime_immutable',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date est obligatoire.']),
                    new GreaterThan([
                        'value' => new \DateTime('+4 days'),
                        'message' => 'La date doit être au moins dans 4 jours.',
                    ])
                ]
            ])
            ->add('capacite', IntegerType::class, [
                'label' => 'Capacité',
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'Capacité'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La capacité est obligatoire.']),
                    new Assert\GreaterThan([
                        'value' => 0,
                        'message' => 'La capacité doit être un nombre positif.'
                    ]),
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type ',
                'choices' => [
                    'CONFÉRENCE' => 'CONFÉRENCE',
                    'SÉMINAIRE' => 'SÉMINAIRE',
                    'CONCERT' => 'CONCERT',
                    'SPORT' => 'SPORT',
                ],
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'Type'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type est obligatoire.']),
                ]
            ])
            ->add('organisateur', TextType::class, [
                'label' => 'Organisateur',
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'Nom '
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'organisateur est obligatoire.']),
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description ',
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'Description détaillée d\'événement',
                    'rows' => 4
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est obligatoire.']),
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('prix', IntegerType::class, [
                'label' => 'Prix ',
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'Prix'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix est obligatoire.']),
                  
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false, 
                'required' => false,
                'attr' => [
                    'class' => 'form-control custom-field',
                ],
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF).',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenements::class,
            'attr' => ['novalidate' => 'novalidate'], 
            'required' => false 
        ]);
    }
}
