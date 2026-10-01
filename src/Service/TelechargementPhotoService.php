<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class TelechargementPhotoService
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/avatars')]
        private readonly string $dossierAvatars,
    ) {
    }

    public function enregistrer(UploadedFile $fichier, Utilisateur $utilisateur): string
    {
        if (!is_dir($this->dossierAvatars)) {
            mkdir($this->dossierAvatars, 0775, true);
        }

        if ($utilisateur->getPhoto() !== null) {
            $ancienne = $this->dossierAvatars.'/'.$utilisateur->getPhoto();
            if (is_file($ancienne)) {
                @unlink($ancienne);
            }
        }

        $nomOriginal = pathinfo($fichier->getClientOriginalName(), \PATHINFO_FILENAME);
        $nomSur = $this->slugger->slug($nomOriginal)->lower();
        $nomFichier = \sprintf('%s-%s.%s', $nomSur, uniqid(), $fichier->guessExtension() ?? 'jpg');

        $fichier->move($this->dossierAvatars, $nomFichier);

        return $nomFichier;
    }
}
