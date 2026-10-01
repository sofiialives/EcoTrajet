<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Léa', 'autocomplete' => 'given-name'],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Martin', 'autocomplete' => 'family-name'],
            ])
            ->add('nomUtilisateur', TextType::class, [
                'label' => 'Nom d’utilisateur',
                'attr' => ['placeholder' => 'lea.martin', 'autocomplete' => 'username'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => ['placeholder' => 'prenom.nom@univ.fr', 'autocomplete' => 'email'],
            ])
            ->add('motDePasse', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => ['placeholder' => '••••••••••', 'autocomplete' => 'new-password', 'data-oeil' => ''],
                    'constraints' => [
                        new Assert\NotBlank(message: 'Veuillez saisir un mot de passe.'),
                        new Assert\Length(
                            min: 8,
                            max: 4096,
                            minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        ),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['placeholder' => '••••••••••', 'autocomplete' => 'new-password', 'data-oeil' => ''],
                ],
            ])
            ->add('conditions', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J’accepte les conditions d’utilisation',
                'constraints' => [
                    new Assert\IsTrue(message: 'Vous devez accepter les conditions d’utilisation.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
