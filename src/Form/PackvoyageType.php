<?php

namespace App\Form;

use App\Entity\Vol;
use App\Entity\Hotel;
use App\Entity\Excursion;
use App\Entity\Packvoyage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Validator\Constraints as Assert;

class PackvoyageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomPack', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'required' => false],
            ])
            ->add('vol', EntityType::class, [
                'class' => Vol::class,
                'choice_label' => fn(Vol $vol) => $vol->getNumeroVol() . ' - ' . $vol->getCompagnie(),
                'required' => false,
                'placeholder' => 'Sélectionnez un vol',
                'attr' => ['class' => 'form-select', 'required' => false]
            ])
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Sélectionnez un hôtel',

                'attr' => ['class' => 'form-control','form-select', 'required' => false],
            ])
            ->add('excursion', EntityType::class, [
                'class' => Excursion::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Sélectionnez une excursion',

                'attr' => ['class' => 'form-control','form-select', 'required' => false],
            ])
            ->add('prixTotal', NumberType::class, [
                'required' => false,
                'empty_data' => null,
                'property_path' => 'prix_total',

                'attr' => ['class' => 'form-control', 'required' => false],
            ])
            ->add('details', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'required' => false],
            ])
            ->add('nombrePlacesRestants', NumberType::class, [
                'required' => false,
                'empty_data' => null,
                'property_path' => 'nombre_places_restants',

                'attr' => ['class' => 'form-control', 'required' => false],
            ]);
          

        // 🔁 Transformateurs pour convertir "" => null (permet de déclencher NotNull)
        $builder->get('prixTotal')->addModelTransformer(new CallbackTransformer(
            fn ($value) => $value,
            fn ($submittedValue) => $submittedValue === '' ? null : $submittedValue
        ));

        $builder->get('nombrePlacesRestants')->addModelTransformer(new CallbackTransformer(
            fn ($value) => $value,
            fn ($submittedValue) => $submittedValue === '' ? null : $submittedValue
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Packvoyage::class,
        ]);
    }
}
