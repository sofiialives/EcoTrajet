<?php

namespace App\Form;

use App\Dto\RechercheTrajetCriteres;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheTrajetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('villeDepart', TextType::class, [
                'label' => 'Ville de départ',
                'required' => false,
                'attr' => ['placeholder' => 'Rouen'],
            ])
            ->add('villeArrivee', TextType::class, [
                'label' => 'Destination',
                'required' => false,
                'attr' => ['placeholder' => 'Paris'],
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('heure', TimeType::class, [
                'label' => 'Heure',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            // Filtres
            ->add('prixMax', NumberType::class, [
                'label' => 'Prix maximum (€)',
                'required' => false,
                'scale' => 2,
                'attr' => ['placeholder' => '20', 'min' => 0],
            ])
            ->add('nombrePlaces', IntegerType::class, [
                'label' => 'Nombre de places',
                'required' => false,
                'attr' => ['placeholder' => '1', 'min' => 1, 'max' => 8],
            ])
            ->add('distanceMax', IntegerType::class, [
                'label' => 'Distance max (km)',
                'required' => false,
                'attr' => ['placeholder' => '200', 'min' => 1],
            ])
            ->add('tri', ChoiceType::class, [
                'label' => 'Trier par',
                'choices' => [
                    'Heure' => RechercheTrajetCriteres::TRI_HEURE,
                    'Prix' => RechercheTrajetCriteres::TRI_PRIX,
                    'Distance' => RechercheTrajetCriteres::TRI_DISTANCE,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RechercheTrajetCriteres::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
