<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Subscription;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }





    /**
     * Fetch Articles
     *
     * Fetches articles by the passed values
     *
     * @param int Records per page
     * @param int Page number
     * @param User Author to fetch transactions from
     * @param bool Should fetch draft
     * @param bool Should fetch banned
     * @param User Filter articles by user subscriptions
     *
     * @return Article[] Array of fetched articles
     */
    public function fetchArticles(
        int $recordsPerPage,
        int $pageNumber = 1,
        User $author = null,
        bool $shouldFetchDraft = false,
        bool $shouldFetchBanned = false,
        User $user = null
    ): array {
        $pageNumber -= 1;

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a');

        $counterQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(a)')
            ->from(Article::class, 'a');

        if(null !== $user) {
            $query
                ->leftJoin(
                    Subscription::class,
                    's',
                    \Doctrine\ORM\Query\Expr\Join::WITH,
                    's.author = a.author'
                )
                ->andWhere("s.user = :user")
                ->setParameter("user", $user);

            $counterQuery
                ->leftJoin(
                    Subscription::class,
                    's',
                    \Doctrine\ORM\Query\Expr\Join::WITH,
                    's.author = a.author'
                )
                ->andWhere("s.user = :user")
                ->setParameter("user", $user);
        }

        if(null !== $author) {
            $query
                ->andWhere('a.author = :author')
                ->setParameter('author', $author);

            $counterQuery
                ->andWhere('a.author = :author')
                ->setParameter('author', $author);
        }

        if(!$shouldFetchDraft) {
            $query
                ->andWhere('a.isPublished = :isPublished')
                ->setParameter('isPublished', true);

            $counterQuery
                ->andWhere('a.isPublished = :isPublished')
                ->setParameter('isPublished', true);
        }

        if(!$shouldFetchBanned) {
            $query
                ->andWhere('a.isBanned = :isBanned')
                ->setParameter('isBanned', false);

            $counterQuery
                ->andWhere('a.isBanned = :isBanned')
                ->setParameter('isBanned', false);
        }
        
        $query
            ->addOrderBy('a.createdDate', "DESC")
            ->addOrderBy('a.id', 'DESC');

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





    /**
     * Get Article Count
     * 
     * @param bool Get Banned
     * 
     * @return int Article Count
     */
    public function getArticleCount(
        bool $getBanned = false
    ): int {
        $counterQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(a)')
            ->from(Article::class, 'a');

        if($getBanned) {
            $counterQuery
                ->andWhere("a.isBanned = :banned")
                ->setParameter("banned", true);
        }

        return (int) $counterQuery->getQuery()->getSingleScalarResult();
    }
}
