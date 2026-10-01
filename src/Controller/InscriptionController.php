<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function inscription(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        Security $security,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_accueil');
        }

        $utilisateur = new Utilisateur();
        $form = $this->createForm(InscriptionType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur->setPassword(
                $passwordHasher->hashPassword($utilisateur, $form->get('motDePasse')->getData()),
            );

            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash('succes', 'Bienvenue sur Eco Trajet, '.$utilisateur->getPrenom().' ! Votre compte a bien été créé.');

            $security->login($utilisateur, 'form_login', 'main');

            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('securite/inscription.html.twig', [
            'form' => $form,
        ]);
    }
}
