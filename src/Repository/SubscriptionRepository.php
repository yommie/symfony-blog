<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Subscription;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Subscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subscription[]    findAll()
 * @method Subscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }





    /**
     * Is Subscribed
     *
     * Checks if a user is subscribed to an author
     *
     * @param User User
     * @param User Author
     * 
     * @throws \Exception
     *
     * @return bool
     */
    public function isSubscribed(
        User $user,
        User $author
    ): bool {
        if($user === $author) {
            throw new \Exception("Users cannot subscribe to themselves");
        }

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('s')
            ->from(Subscription::class, 's')
            ->where("s.user = :user")
            ->andWhere("s.author = :author")
            ->setParameters([
                "user" => $user,
                "author" => $author
            ])
            ->getQuery()
            ->getOneOrNullResult();

        return null !== $query;
    }





    /**
     * Fetch Subscriptions
     *
     * @param int Records per page
     * @param int Page number
     * @param User User to fetch subscriptions from
     *
     * @return Subscription[] Array of fetched subscriptions
     */
    public function fetchSubscriptions(
        int $recordsPerPage,
        int $pageNumber = 1,
        User $user
    ): array {
        $pageNumber -= 1;

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('s')
            ->from(Subscription::class, 's')
            ->leftJoin("s.author", "a")
            ->where('s.user = :user')
            ->setParameter("user", $user);

        $counterQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(s)')
            ->from(Subscription::class, 's')
            ->where('s.user = :user')
            ->setParameter("user", $user);
        
        $query->addOrderBy('a.username', "ASC");

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
