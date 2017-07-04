<?php

namespace AppBundle\Repository;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends \Doctrine\ORM\EntityRepository {

    public function getArticleByIdWithJoin($id) {
        $qb = $this->createQueryBuilder('a');
        $qb->leftJoin('a.image', 'i')
                ->addSelect('i')
                ->leftJoin('a.commentaires', 'c')
                ->addSelect('c')
                ->leftJoin('a.tags', 't')
                ->addSelect('t')
                ->where('a.id = ?1')
                ->setParameter(1, $id)
                ->orderBy('a.date', 'DESC');

        $query = $qb->getQuery();
        $article = $query->getOneOrNullResult();
        return $article;
    }

    public function getArticleBySlugWithJoin($slug) {
        $qb = $this->createQueryBuilder('a');
        $qb->leftJoin('a.image', 'i')
                ->addSelect('i')
                ->leftJoin('a.commentaires', 'c')
                ->addSelect('c')
                ->leftJoin('a.tags', 't')
                ->addSelect('t')
                ->where('a.slug = ?1')
                ->setParameter(1, $slug);

        $query = $qb->getQuery();
        $article = $query->getOneOrNullResult();
        return $article;
    }

    public function getArticles() {
        $qb = $this->createQueryBuilder('a');
        $qb->leftJoin('a.image', 'i')
                ->addSelect('i')
                ->leftJoin('a.commentaires', 'c')
                ->addSelect('c')
                ->leftJoin('a.tags', 't')
                ->addSelect('t')
                ->orderBy('a.date', 'DESC');

        $query = $qb->getQuery();
        //   $articles = $query->getArrayResult();
        $articles = $query->getResult();

        return $articles;
    }

    public function getArticlesWithPagination($offset, $limit) {
        $qb = $this->createQueryBuilder('a');
        $qb->leftJoin('a.image', 'i')
                ->addSelect('i')
                ->leftJoin('a.tags', 't')
                ->addSelect('t')
                ->orderBy('a.date', 'DESC')
                ->where('a.publication = true')
                ->setFirstResult($offset)
                ->setMaxResults($limit)
        ;

        $query = $qb->getQuery();

        return new \Doctrine\ORM\Tools\Pagination\Paginator($query);
    }

    public function getArticlesByTagWithJoin($tag) {
        $qb = $this->createQueryBuilder('a');
        $qb->leftJoin('a.image', 'i')
                ->addSelect('i')
                ->leftJoin('a.commentaires', 'c')
                ->addSelect('c')
                ->leftJoin('a.tags', 't')
                ->addSelect('t')
                ->where('t = ?1')
                ->andWhere('a.publication = 1')
                ->setParameter(1, $tag)
                ->orderBy('a.date', 'DESC');

        return $query = $qb->getQuery();
        //   $articles = $query->getArrayResult();
        //  return $articles;
    }

    public function getCountByTag($tag) {

        $qb = $this->createQueryBuilder('a');
        $qb->select('count(a)')
                ->leftJoin('a.tags', 't')
                ->where('t = ?1')
                ->andWhere('a.publication = 1')
                ->setParameter(1, $tag)
                ->orderBy('a.date', 'DESC');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getLatestArticles($limit) {

        $qb = $this->createQueryBuilder('a');
        $qb->where('a.publication = 1')
                ->orderBy('a.date', 'DESC')
                ->setMaxResults($limit);

        $query = $qb->getQuery();
        $articles = $query->getArrayResult();


        return $articles;
    }

    public function getArticlesByYear($year) {
        $debut = $year . '-01-01';
        $fin = $year . '-12-31';

        $qb = $this->createQueryBuilder('a');
        $qb->where('a.date>= ?1 AND a.date<= ?2 ')
                ->setParameter(1, $debut)
                ->setParameter(2, $fin)
                ->orderBy('a.date', 'DESC');

        $query = $qb->getQuery();
        $articles = $query->getResult();


        return $articles;
    }

    public function getCurrentYear($limit = 5) {
        if ((int) $limit) {
            return $this->createQueryBuilder('a')
                            ->select('SUBSTRING(a.date,1,4)')
                            ->distinct()
                            ->orderBy('a.date', 'DESC')
                            ->setMaxResults($limit)
                            ->getQuery()
                            ->getResult();
        }
        return null;
    }

    //
//    public function getCurrentYear() {
//
//        $year = date("Y");
//
//        return $year;
//    }
//
}
