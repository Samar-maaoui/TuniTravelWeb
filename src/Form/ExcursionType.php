<?php

namespace App\Form;

use App\Entity\Excursion;
use App\Entity\Guide;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ExcursionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $wilayas = [
            'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan', 'Bizerte', 'Béja',
            'Jendouba', 'Kef', 'Siliana', 'Sousse', 'Monastir', 'Mahdia', 'Kairouan', 'Kasserine',
            'Sidi Bouzid', 'Sfax', 'Gabès', 'Medenine', 'Tataouine', 'Gafsa', 'Tozeur', 'Kebili',
        ];

        $builder
            ->add('nom', TextType::class)
            ->add('villeDepart', ChoiceType::class, [
                'choices' => array_combine($wilayas, $wilayas),
                'placeholder' => 'Choisissez une Ville de départ',
            ])
            ->add('lieu', ChoiceType::class, [
                'choices' => array_combine($wilayas, $wilayas),
                'placeholder' => 'Choisissez une destination',
            ])
            ->add('prix', NumberType::class)
            ->add('date_depart', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('date_retour', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de retour est requise.']),
                    new Assert\Callback(function ($value, ExecutionContextInterface $context) {
                        $form = $context->getRoot();
                        $dateDepart = $form->get('date_depart')->getData();
                        if ($dateDepart && $value < $dateDepart) {
                            $context->buildViolation('La date de retour doit être après ou égale à la date de départ.')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('description', TextareaType::class)
            ->add('nombre_max_participants', NumberType::class)
            ->add('image', FileType::class, [
                'label' => 'Image (fichier JPG ou PNG)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('guide', EntityType::class, [
                'class' => Guide::class,
                'choice_label' => 'nom',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Excursion::class,
        ]);
    }
}
