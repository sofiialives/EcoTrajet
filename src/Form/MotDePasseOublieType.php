<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MotDePasseOublieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => 'Adresse e-mail',
            'attr' => ['placeholder' => 'prenom.nom@univ.fr', 'autocomplete' => 'email'],
            'constraints' => [
                new Assert\NotBlank(message: 'Veuillez saisir votre adresse e-mail.'),
                new Assert\Email(message: 'Veuillez saisir une adresse e-mail valide.'),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
