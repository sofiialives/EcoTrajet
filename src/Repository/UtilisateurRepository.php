<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(\sprintf('Les instances de "%s" ne sont pas prises en charge.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->flush();
    }

    public function findParEmail(string $email): ?Utilisateur
    {
        return $this->findOneBy(['email' => mb_strtolower($email)]);
    }

    public function findParResetToken(string $token): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.resetToken = :token')
            ->andWhere('u.resetTokenExpireLe > :maintenant')
            ->setParameter('token', $token)
            ->setParameter('maintenant', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
