<?php

namespace App\Form;

use App\Entity\Packvoyage;
use App\Entity\Post;
use App\Repository\PackvoyageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PostType extends AbstractType
{
    private PackvoyageRepository $repo;

    public function __construct(PackvoyageRepository $repo)
    {
        $this->repo = $repo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('titre', TextType::class, [
                'required' => true,
                'label' => 'Titre',
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'Titre',
                    'required' => false,
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'Description détaillée',
                    'rows' => 4,
                    'readonly' => true,
                    'id' => 'post_description',
                ],
            ])
            ->add('date_publication', DateType::class, [
                'required' => false,
                'label' => 'Date de Publication',
                'widget' => 'single_text',
                'empty_data' => null,
                'disabled' => $isEdit, // 💡 désactivé si modification
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'ex: 2025-01-01', // facultatif
                ],
            ])
            ->add('date_expiration', DateType::class, [
                'required' => false,
                'label' => 'Date d\'Expiration',
                'widget' => 'single_text',
                'empty_data' => null,
                'attr' => [
                    'class' => 'form-control custom-field',
                    'placeholder' => 'ex: 2026-01-01',
                ],
            ])
            
            
            
            ->add('packvoyage', EntityType::class, [
                'required' => true, // ✅ correspond à l'Assert\NotNull
                'class' => Packvoyage::class,
                'choice_label' => 'nomPack',
                'label' => 'Pack Voyage',
                'placeholder' => 'Sélectionnez un pack', // ✅ permet un champ vide par défaut
                'attr' => [
                    'class' => 'form-control custom-field',
                    'id' => 'post_packvoyage',
                ],
            ]);
            
        // 🔁 Ajout du listener PRE_SUBMIT
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (isset($data['packvoyage']) && $data['packvoyage']) {
                $pack = $this->repo->find($data['packvoyage']);
                if ($pack) {
                    // Vol
                    $vol = $pack->getVol();
                    $volInfo = $vol ? $vol->getNumeroVol() . ' - ' . $vol->getCompagnie() : 'Non défini';
        
                    // Hôtel (objet → string via getNom)
                    $hotelEntity = $pack->getHotel();
                    $hotel = $hotelEntity ? $hotelEntity->getNom() : 'Non défini';
        
                    // Excursion (objet → string via getNom)
                    $excursionEntity = $pack->getExcursion();
                    $excursion = $excursionEntity ? $excursionEntity->getNom() : 'Non défini';
        
                    // Description enrichie
                    $description = "✈️ Vol : " . $volInfo;
                    $description .= " 🏨 Hôtel : " . $hotel;
                    $description .= " 🗺️ Excursion : " . $excursion;
                    $description .= "\n" . $pack->getDetails();
        
                    // Injection dans le formulaire
                    $data['description'] = $description;
                    $event->setData($data);
                }
            }
        }); }
        

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'is_edit' => false, // <- option personnalisée

        ]);
    }
}
