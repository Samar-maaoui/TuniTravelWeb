<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Utilisateur;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('photo', FileType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '2M',
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (jpg, png, gif).',
                    ])
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est requis.']),
                    new Assert\Length(['min' => 2, 'minMessage' => 'Le nom doit contenir au moins 2 caractères.']),
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est requis.']),
                    new Assert\Length(['min' => 2, 'minMessage' => 'Le prénom doit contenir au moins 2 caractères.']),
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Lemail est requis.']),
                    new Assert\Email(['message' => 'Adresse email invalide.']),
                ]
            ])
            ->add('cin', TextType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le CIN est requis.']),
                    new Assert\Length(['min' => 6, 'max' => 12, 'minMessage' => 'CIN trop court.', 'maxMessage' => 'CIN trop long.']),
                ]
            ])
            ->add('telephone', TelType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de téléphone est requis.']),
                    new Assert\Regex([
                        'pattern' => '/^[0-9\+\s\-\(\)]{8,}$/',
                        'message' => 'Numéro de téléphone invalide.',
                    ]),
                ]
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => false,
                'widget' => 'single_text',
                'required' => true,
                'html5' => true,
                'empty_data' => null,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de naissance est requise.'
                    ]),
                    new Assert\Callback(function ($dateNaissance, ExecutionContextInterface $context) {
                        if ($dateNaissance === null) {
                            $context->buildViolation('La date de naissance est requise.')
                                ->atPath('dateNaissance')
                                ->addViolation();
                            return;
                        }
                        
                        $today = new \DateTime();
                        $interval = $today->diff($dateNaissance);
                        $age = $interval->y;
            
                        if ($age < 18) {
                            $context->buildViolation("L'utilisateur doit avoir au moins 18 ans.")
                                ->atPath('dateNaissance')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'adresse est requise.']),
                    new Assert\Length(['min' => 5, 'minMessage' => 'Adresse trop courte.']),
                ]
            ])
            ->add('mot_de_passe', PasswordType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le mot de passe est requis.']),
                    new Assert\Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins 6 caractères.']),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
