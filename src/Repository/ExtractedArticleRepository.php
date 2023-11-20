<?php

namespace App\Repository;

use App\Entity\ExtractedArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExtractedArticle|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExtractedArticle|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExtractedArticle[]    findAll()
 * @method ExtractedArticle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExtractedArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtractedArticle::class);
    }

    public function add(ExtractedArticle $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(ExtractedArticle $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findArticleByUrl($url): ?ExtractedArticle
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.url = :val')
            ->setParameter('val', $url)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findArticleByTranslatedText($text): ?ExtractedArticle
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.translated_content LIKE :val')
            ->setParameter('val', '%' . $text . '%')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
