<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ChangementMotDePasseType;
use App\Form\ProfilType;
use App\Service\TelechargementPhotoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        TelechargementPhotoService $photoService,
    ): Response {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        $formProfil = $this->createForm(ProfilType::class, $utilisateur);
        $formProfil->handleRequest($request);

        if ($formProfil->isSubmitted() && $formProfil->isValid()) {
            $photo = $formProfil->get('photoFichier')->getData();
            if ($photo !== null) {
                $utilisateur->setPhoto($photoService->enregistrer($photo, $utilisateur));
            }

            $em->flush();
            $this->addFlash('succes', 'Vos modifications ont bien été enregistrées.');

            return $this->redirectToRoute('app_profil');
        }

        $formMotDePasse = $this->createForm(ChangementMotDePasseType::class);
        $formMotDePasse->handleRequest($request);

        if ($formMotDePasse->isSubmitted() && $formMotDePasse->isValid()) {
            $utilisateur->setPassword(
                $passwordHasher->hashPassword($utilisateur, $formMotDePasse->get('nouveauMotDePasse')->getData()),
            );
            $em->flush();
            $this->addFlash('succes', 'Votre mot de passe a bien été modifié.');

            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/index.html.twig', [
            'form_profil' => $formProfil,
            'form_mot_de_passe' => $formMotDePasse,
        ]);
    }
}
