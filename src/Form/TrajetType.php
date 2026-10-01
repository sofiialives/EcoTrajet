<?php

namespace App\Form;

use App\Entity\Trajet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrajetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('villeDepart', TextType::class, [
                'label' => 'Ville de départ',
                'attr' => ['placeholder' => 'Rouen'],
            ])
            ->add('villeArrivee', TextType::class, [
                'label' => 'Ville d’arrivée',
                'attr' => ['placeholder' => 'Paris'],
            ])
            ->add('distanceKm', IntegerType::class, [
                'label' => 'Distance (km)',
                'attr' => ['placeholder' => '120', 'min' => 1],
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('heure', TimeType::class, [
                'label' => 'Heure de départ',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('nombrePlaces', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => ['placeholder' => '3', 'min' => 1, 'max' => 8],
            ])
            ->add('prixParPlace', MoneyType::class, [
                'label' => 'Prix par place (€)',
                'currency' => 'EUR',
                'attr' => ['placeholder' => '15'],
            ])
            ->add('vehicule', TextType::class, [
                'label' => 'Véhicule',
                'required' => false,
                'attr' => ['placeholder' => 'Renault Clio V · grise'],
            ])
            ->add('pointRendezVous', TextType::class, [
                'label' => 'Point de rendez-vous',
                'attr' => ['placeholder' => 'Parking du Zénith, entrée B'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Départ ponctuel, musique tranquille, pause possible à mi-chemin…',
                    'rows' => 4,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trajet::class,
        ]);
    }
}
