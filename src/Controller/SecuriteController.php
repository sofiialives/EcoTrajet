<?php

namespace App\Controller;

use App\Form\MotDePasseOublieType;
use App\Form\ReinitialisationMotDePasseType;
use App\Service\MotDePasseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecuriteController extends AbstractController
{
    #[Route('/connexion', name: 'app_connexion')]
    public function connexion(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('securite/connexion.html.twig', [
            'erreur' => $authenticationUtils->getLastAuthenticationError(),
            'dernier_email' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/deconnexion', name: 'app_deconnexion')]
    public function deconnexion(): never
    {
        throw new \LogicException('Cette méthode est interceptée par le firewall.');
    }

    #[Route('/mot-de-passe-oublie', name: 'app_mot_de_passe_oublie')]
    public function motDePasseOublie(Request $request, MotDePasseService $motDePasseService): Response
    {
        $form = $this->createForm(MotDePasseOublieType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $motDePasseService->envoyerLien($form->get('email')->getData());

            return $this->render('securite/mot_de_passe_envoye.html.twig');
        }

        return $this->render('securite/mot_de_passe_oublie.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reinitialisation/{token}', name: 'app_mot_de_passe_reinitialiser')]
    public function reinitialiser(string $token, Request $request, MotDePasseService $motDePasseService): Response
    {
        $utilisateur = $motDePasseService->trouverParToken($token);
        if ($utilisateur === null) {
            $this->addFlash('erreur', 'Ce lien de réinitialisation est invalide ou a expiré.');

            return $this->redirectToRoute('app_mot_de_passe_oublie');
        }

        $form = $this->createForm(ReinitialisationMotDePasseType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $motDePasseService->reinitialiser($utilisateur, $form->get('motDePasse')->getData());
            $this->addFlash('succes', 'Votre mot de passe a bien été modifié. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_connexion');
        }

        return $this->render('securite/reinitialisation.html.twig', [
            'form' => $form,
        ]);
    }
}
