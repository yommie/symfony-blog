<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Notification;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
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





    /**
     * Fetch Notifications
     *
     * @param int Records per page
     * @param int Page number
     * @param User Notification Subject
     *
     * @return Notification[] Array of fetched notifications
     */
    public function fetchNotifications(
        int $recordsPerPage,
        int $pageNumber = 1,
        User $subject
    ): array {
        $pageNumber -= 1;

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('n')
            ->from(Notification::class, 'n')
            ->where('n.subject = :subject')
            ->andWhere("n.isSeen = :isSeen")
            ->setParameters([
                "subject" => $subject,
                "isSeen" => false
            ]);

        $counterQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(n)')
            ->from(Notification::class, 'n')
            ->where('n.subject = :subject')
            ->andWhere("n.isSeen = :isSeen")
            ->setParameters([
                "subject" => $subject,
                "isSeen" => false
            ]);
        
        $query->addOrderBy('n.createdDate', "DESC");

        if(null !== $pageNumber && is_int($pageNumber)) {
            $recordsPerPage = (null === $recordsPerPage || !is_integer($recordsPerPage)) ? false : $recordsPerPage;

            $query->setFirstResult($recordsPerPage * $pageNumber);

            if($recordsPerPage) {
                $query->setMaxResults($recordsPerPage);
            }
        }

        $finishedQuery = $query->getQuery();

        $paginator = new Paginator($finishedQuery);

        $totalMatched = $counterQuery->getQuery()->getSingleScalarResult();

        return array(
            'paginator' => $paginator,
            'totalMatched' => (int) $totalMatched
        );
    }
}
