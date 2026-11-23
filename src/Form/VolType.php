<?php

namespace App\Form;

use App\Entity\Vol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero_vol')
            ->add('compagnie')
            ->add('prix')
            ->add('disponible')
            ->add('aero_depart')
            ->add('aero_arrivee')
            ->add('date_depart', null, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('date_arrivee', null, [
                'widget' => 'single_text',
                'required' => true,
            ])
        ;
    }
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vol::class,
        ]);
    }
}
