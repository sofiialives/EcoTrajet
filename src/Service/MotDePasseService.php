<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class MotDePasseService
{
    private const DUREE_VALIDITE = '+1 hour';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }


    public function envoyerLien(string $email): void
    {
        $utilisateur = $this->utilisateurRepository->findParEmail($email);
        if ($utilisateur === null) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $utilisateur->setResetToken($token);
        $utilisateur->setResetTokenExpireLe(new \DateTimeImmutable(self::DUREE_VALIDITE));
        $this->em->flush();

        $lien = $this->urlGenerator->generate(
            'app_mot_de_passe_reinitialiser',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $message = (new TemplatedEmail())
            ->from(new Address('no-reply@ecotrajet.fr', 'Eco Trajet'))
            ->to($utilisateur->getEmail())
            ->subject('Réinitialisation de votre mot de passe — Eco Trajet')
            ->htmlTemplate('emails/reinitialisation.html.twig')
            ->context([
                'utilisateur' => $utilisateur,
                'lien' => $lien,
            ]);

        $this->mailer->send($message);
    }

    public function trouverParToken(string $token): ?Utilisateur
    {
        return $this->utilisateurRepository->findParResetToken($token);
    }

    public function reinitialiser(Utilisateur $utilisateur, string $motDePasseEnClair): void
    {
        $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, $motDePasseEnClair));
        $utilisateur->setResetToken(null);
        $utilisateur->setResetTokenExpireLe(null);
        $this->em->flush();
    }
}
