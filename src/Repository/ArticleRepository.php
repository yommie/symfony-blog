<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Article;
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
     *
     * @return Article[] Array of fetched articles
     */
    public function fetchArticles(
        int $recordsPerPage,
        int $pageNumber = 1,
        User $author = null,
        bool $shouldFetchDraft = false,
        bool $shouldFetchBanned = false
    ) {
        $pageNumber -= 1;

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a');

        $counterQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(a)')
            ->from(Article::class, 'a');

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
        
        $query->addOrderBy('a.createdDate', "DESC");

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

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
