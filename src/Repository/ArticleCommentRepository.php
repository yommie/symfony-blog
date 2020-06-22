<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\ArticleComment;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ArticleComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleComment[]    findAll()
 * @method ArticleComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleComment::class);
    }





    /**
     * Fetch Article Comments
     *
     * Fetches article comments by the passed values
     *
     * @param int Records per page
     * @param int Page number
     * @param Article Article to fetch comments from
     *
     * @return ArticleComment[] Array of fetched articles
     */
    public function fetchArticleComments(
        int $recordsPerPage,
        int $pageNumber = 1,
        Article $article
    ): array {
        $pageNumber -= 1;

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from(ArticleComment::class, 'a')
            ->where("a.article = :article")
            ->setParameter("article", $article);

        $counterQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(a)')
            ->from(ArticleComment::class, 'a')
            ->where("a.article = :article")
            ->setParameter("article", $article);
        
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
}
