<?php

namespace App\Repository;

use App\Entity\TrustedSites;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TrustedSites|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrustedSites|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrustedSites[]    findAll()
 * @method TrustedSites[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrustedSitesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrustedSites::class);
    }

    public function add(TrustedSites $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(TrustedSites $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findTrustedSiteByDomain($domain): ?TrustedSites
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.domain = :val')
            ->setParameter('val', $domain)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getTopTrustedSites(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.percentage', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();
    }
}
