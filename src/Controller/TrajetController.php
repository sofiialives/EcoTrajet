<?php

namespace App\Controller;

use App\Dto\RechercheTrajetCriteres;
use App\Entity\Trajet;
use App\Entity\Utilisateur;
use App\Form\RechercheTrajetType;
use App\Form\TrajetType;
use App\Repository\TrajetRepository;
use App\Service\TrajetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TrajetController extends AbstractController
{
    #[Route('/trajets', name: 'app_trajet_rechercher')]
    public function rechercher(Request $request, TrajetRepository $trajetRepository): Response
    {
        $criteres = new RechercheTrajetCriteres();
        $form = $this->createForm(RechercheTrajetType::class, $criteres);
        $form->handleRequest($request);

        $trajets = $trajetRepository->rechercher($criteres);

        return $this->render('trajet/rechercher.html.twig', [
            'form' => $form,
            'trajets' => $trajets,
            'criteres' => $criteres,
        ]);
    }

    #[Route('/trajets/creer', name: 'app_trajet_creer')]
    #[IsGranted('ROLE_USER')]
    public function creer(Request $request, TrajetService $trajetService): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        $trajet = new Trajet();
        $form = $this->createForm(TrajetType::class, $trajet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trajetService->publier($trajet, $utilisateur);
            $this->addFlash('succes', 'Votre trajet '.$trajet->getItineraire().' a bien été publié.');

            return $this->redirectToRoute('app_mes_trajets');
        }

        return $this->render('trajet/creer.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/trajets/{id<\d+>}', name: 'app_trajet_details')]
    public function details(Trajet $trajet): Response
    {
        return $this->render('trajet/details.html.twig', [
            'trajet' => $trajet,
        ]);
    }

    #[Route('/trajets/{id<\d+>}/modifier', name: 'app_trajet_modifier')]
    #[IsGranted('ROLE_USER')]
    public function modifier(Trajet $trajet, Request $request, TrajetService $trajetService): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        if (!$trajetService->peutEtreModifiePar($trajet, $utilisateur)) {
            $this->addFlash('erreur', 'Vous ne pouvez pas modifier ce trajet.');

            return $this->redirectToRoute('app_mes_trajets');
        }

        $form = $this->createForm(TrajetType::class, $trajet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trajetService->enregistrerModification();
            $this->addFlash('succes', 'Le trajet a bien été modifié.');

            return $this->redirectToRoute('app_trajet_details', ['id' => $trajet->getId()]);
        }

        return $this->render('trajet/modifier.html.twig', [
            'form' => $form,
            'trajet' => $trajet,
        ]);
    }

    #[Route('/trajets/{id<\d+>}/annuler', name: 'app_trajet_annuler', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function annuler(Trajet $trajet, Request $request, TrajetService $trajetService): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        if ($trajet->getConducteur() !== $utilisateur) {
            $this->addFlash('erreur', 'Vous ne pouvez annuler que vos propres trajets.');

            return $this->redirectToRoute('app_mes_trajets');
        }

        if (!$this->isCsrfTokenValid('annuler_trajet_'.$trajet->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('erreur', 'Jeton de sécurité invalide, veuillez réessayer.');

            return $this->redirectToRoute('app_mes_trajets');
        }

        try {
            $trajetService->annuler($trajet);
            $this->addFlash('succes', 'Le trajet '.$trajet->getItineraire().' a été annulé. Les passagers ont été prévenus.');
        } catch (\DomainException $e) {
            $this->addFlash('erreur', $e->getMessage());
        }

        return $this->redirectToRoute('app_mes_trajets');
    }
}
