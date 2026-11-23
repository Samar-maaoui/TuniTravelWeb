<?php

namespace App\Form;

use App\Entity\Reservationpackvoyage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationpackvoyageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_reservation', null, [
                'widget' => 'single_text',
            ])
            ->add('statut')
            ->add('statut_paiement')
            ->add('pack_id')
            ->add('post_id')
            ->add('places_demandees')
            ->add('montant_total');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservationpackvoyage::class,
        ]);
    }
}
