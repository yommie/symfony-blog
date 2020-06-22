<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Notification;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }





    /**
     * Get Notification Count
     *
     * @param User Notification Subject
     *
     * @return int Notification Count
     */
    public function getNotificationCount(User $subject): int {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(n)')
            ->from(Notification::class, 'n')
            ->where("n.subject = :subject")
            ->andWhere("n.isSeen = :isSeen")
            ->setParameters([
                "subject" => $subject,
                "isSeen" => false
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
