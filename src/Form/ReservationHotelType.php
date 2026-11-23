<?php

namespace App\Form;

use App\Entity\ReservationHotel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReservationHotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'choice_label' => 'nom',
                'label' => 'Hôtel'
            ])
            ->add('check_in_date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date d’arrivée'
            ])
            ->add('check_out_date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de départ'
            ])
            ->add('type_chambre', ChoiceType::class, [
                'label' => 'Type de chambre',
                'choices'  => [
                    'Simple' => 'simple',
                    'Double' => 'double',
                    'MVP'    => 'mvp',
                ],
                'expanded' => false, // si tu veux une liste déroulante
                'multiple' => false, // une seule option possible
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationHotel::class,
        ]);
    }
}
