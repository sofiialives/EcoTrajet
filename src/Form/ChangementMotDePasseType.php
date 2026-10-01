<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

class ChangementMotDePasseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ancienMotDePasse', PasswordType::class, [
                'label' => 'Ancien mot de passe',
                'mapped' => false,
                'attr' => ['autocomplete' => 'current-password', 'data-oeil' => ''],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez saisir votre ancien mot de passe.'),
                    new UserPassword(message: 'L’ancien mot de passe est incorrect.'),
                ],
            ])
            ->add('nouveauMotDePasse', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => ['autocomplete' => 'new-password', 'data-oeil' => ''],
                    'constraints' => [
                        new Assert\NotBlank(message: 'Veuillez saisir un nouveau mot de passe.'),
                        new Assert\Length(
                            min: 8,
                            max: 4096,
                            minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        ),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le nouveau mot de passe',
                    'attr' => ['autocomplete' => 'new-password', 'data-oeil' => ''],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
