<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cette adresse e-mail.')]
#[UniqueEntity(fields: ['nomUtilisateur'], message: 'Ce nom d’utilisateur est déjà pris.')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre prénom.')]
    #[Assert\Length(max: 100, maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre nom.')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $nom = null;

    #[ORM\Column(length: 60, unique: true)]
    #[Assert\NotBlank(message: 'Veuillez choisir un nom d’utilisateur.')]
    #[Assert\Length(
        min: 3,
        max: 60,
        minMessage: 'Le nom d’utilisateur doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom d’utilisateur ne peut pas dépasser {{ limit }} caractères.',
    )]
    #[Assert\Regex(
        pattern: '/^[a-z0-9._-]+$/i',
        message: 'Le nom d’utilisateur ne peut contenir que des lettres, chiffres, points, tirets et underscores.',
    )]
    private ?string $nomUtilisateur = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre adresse e-mail.')]
    #[Assert\Email(message: 'Veuillez saisir une adresse e-mail valide.')]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $dateInscription;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $resetTokenExpireLe = null;

    /** @var Collection<int, Trajet> */
    #[ORM\OneToMany(targetEntity: Trajet::class, mappedBy: 'conducteur', orphanRemoval: true)]
    #[ORM\OrderBy(['date' => 'DESC', 'heure' => 'DESC'])]
    private Collection $trajets;

    /** @var Collection<int, Reservation> */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'passager', orphanRemoval: true)]
    #[ORM\OrderBy(['dateReservation' => 'DESC'])]
    private Collection $reservations;

    public function __construct()
    {
        $this->dateInscription = new \DateTimeImmutable();
        $this->trajets = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNomUtilisateur(): ?string
    {
        return $this->nomUtilisateur;
    }

    public function setNomUtilisateur(string $nomUtilisateur): static
    {
        $this->nomUtilisateur = mb_strtolower($nomUtilisateur);

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower($email);

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getDateInscription(): \DateTimeImmutable
    {
        return $this->dateInscription;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getResetTokenExpireLe(): ?\DateTimeImmutable
    {
        return $this->resetTokenExpireLe;
    }

    public function setResetTokenExpireLe(?\DateTimeImmutable $resetTokenExpireLe): static
    {
        $this->resetTokenExpireLe = $resetTokenExpireLe;

        return $this;
    }

    /** @return Collection<int, Trajet> */
    public function getTrajets(): Collection
    {
        return $this->trajets;
    }

    public function addTrajet(Trajet $trajet): static
    {
        if (!$this->trajets->contains($trajet)) {
            $this->trajets->add($trajet);
            $trajet->setConducteur($this);
        }

        return $this;
    }

    public function removeTrajet(Trajet $trajet): static
    {
        $this->trajets->removeElement($trajet);

        return $this;
    }

    /** @return Collection<int, Reservation> */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setPassager($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        $this->reservations->removeElement($reservation);

        return $this;
    }

    public function getNomComplet(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    public function getNomCourt(): string
    {
        $initiale = $this->nom !== null && $this->nom !== '' ? mb_strtoupper(mb_substr($this->nom, 0, 1)).'.' : '';

        return trim($this->prenom.' '.$initiale);
    }

    public function getInitiales(): string
    {
        $i = '';
        foreach ([$this->prenom, $this->nom] as $part) {
            if ($part !== null && $part !== '') {
                $i .= mb_strtoupper(mb_substr($part, 0, 1));
            }
        }

        return $i !== '' ? $i : '?';
    }


    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials(): void
    {
    }
}
