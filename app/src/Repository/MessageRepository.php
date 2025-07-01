<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findInboxMessages(User $user): Query
    {
        return $this->createQueryBuilder('m')
            ->where('m.recipient = :user AND m.lastSender != :username AND m.isRecipientDelete = false')
            ->orWhere('m.sender = :user AND m.lastSender != :username AND m.isSenderDelete = false') // message principal (pas une réponse)
            ->setParameter('user', $user)
            ->setParameter('username', $user->getName())
            ->orderBy('m.modify_at', 'DESC')
            ->getQuery();
    }

    public function findSentMessages(User $user): Query
    {
        return $this->createQueryBuilder('m')
            ->where('m.lastSender = :username AND m.isRecipientDelete = false AND m.recipient = :user')
            ->orWhere('m.sender = :user AND m.lastSender = :username AND m.isSenderDelete = false')
            ->setParameter('username', $user->getName())
            ->setParameter('user', $user)
            ->orderBy('m.modify_at', 'DESC')
            ->getQuery(); // ⬅️ retourne un Query, pas getResult()
    }
    public function getFavoriteMessagesQuery(User $user): Query
    {
        return $this->createQueryBuilder('m')
            ->where(':user MEMBER OF m.favorite')
            ->andWhere('m.sender = :user OR m.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('m.modify_at', 'DESC')
            ->getQuery(); // ⬅️ retourne un Query, pas getResult()
    }


//    /**
//     * @return Message[] Returns an array of Message objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Message
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
