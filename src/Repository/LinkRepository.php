<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Link>
 *
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    /**
     * Find all links with their associated tags and users
     * Uses proper JOIN to avoid N+1 queries
     * @return Link[]
     */
    public function findAllWithTagsAndUsers(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.tags', 't')
            ->addSelect('t')
            ->leftJoin('l.user', 'u')
            ->addSelect('u')
            ->orderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find links by user with their tags
     * @param int $userId
     * @return Link[]
     */
    public function findByUserWithTags(int $userId): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.tags', 't')
            ->addSelect('t')
            ->where('l.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find links by tag with their users
     * @param int $tagId
     * @return Link[]
     */
    public function findByTagWithUsers(int $tagId): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.tags', 't')
            ->leftJoin('l.user', 'u')
            ->addSelect('t', 'u')
            ->where('t.id = :tagId')
            ->setParameter('tagId', $tagId)
            ->orderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
