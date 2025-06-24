<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
    public function findByRole(string $role): array
    {
        $qb = $this->createQueryBuilder('u');
        return $qb->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"'.$role.'"%')
            ->getQuery()
            ->getResult();
    }

    public function getUsersByRoleQueryBuilder(string $role)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"'.$role.'"%');
    }

    public function findAdminsAndEmployeesQuery(): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :admin')
            ->orWhere('u.roles LIKE :employee')
            ->setParameter('admin', '%"ROLE_ADMIN"%')
            ->setParameter('employee', '%"ROLE_EMPLOYEE"%')
            ->orderBy('u.name', 'ASC')
            ->getQuery(); // ⬅️ retourne un Query, pas getResult()
    }

    public function getUsersByAdminOrEmployeeRoleQueryBuilder()
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :admin')
            ->orWhere('u.roles LIKE :employee')
            ->setParameter('admin', '%"ROLE_ADMIN"%')
            ->setParameter('employee', '%"ROLE_EMPLOYEE"%')
            ->orderBy('u.name', 'ASC');
    }


//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
