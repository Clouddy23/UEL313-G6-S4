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

    /**
     * Search links by title or description with tags and users
     * @param string $searchTerm
     * @return Link[]
     */
    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.tags', 't')
            ->addSelect('t')
            ->leftJoin('l.user', 'u')
            ->addSelect('u')
            ->where('l.title LIKE :searchTerm OR l.desc LIKE :searchTerm OR l.url LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent links (last N days) with tags and users
     * @param int $days
     * @return Link[]
     */
    public function findRecentLinks(int $days = 7): array
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . $days . 'D'));

        return $this->createQueryBuilder('l')
            ->leftJoin('l.tags', 't')
            ->addSelect('t')
            ->leftJoin('l.user', 'u')
            ->addSelect('u')
            ->where('l.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }



    /**
     * Get links count by user
     * @param int $userId
     * @return int
     */
    public function countByUser(int $userId): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find links with pagination
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function findWithPagination(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;

        $links = $this->createQueryBuilder('l')
            ->leftJoin('l.tags', 't')
            ->addSelect('t')
            ->leftJoin('l.user', 'u')
            ->addSelect('u')
            ->orderBy('l.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $total = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'links' => $links,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit)
        ];
    }

    /**
     * Find links by multiple tags (AND condition)
     * @param array $tagIds
     * @return Link[]
     */
    public function findByMultipleTags(array $tagIds): array
    {
        if (empty($tagIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.tags', 't')
            ->addSelect('t')
            ->leftJoin('l.user', 'u')
            ->addSelect('u');

        foreach ($tagIds as $index => $tagId) {
            $qb->andWhere($qb->expr()->exists(
                $this->getEntityManager()->createQueryBuilder()
                    ->select('1')
                    ->from('App\Entity\Link', 'l' . $index)
                    ->join('l' . $index . '.tags', 't' . $index)
                    ->where('l' . $index . '.id = l.id')
                    ->andWhere('t' . $index . '.id = :tagId' . $index)
                    ->getDQL()
            ))
                ->setParameter('tagId' . $index, $tagId);
        }

        return $qb->orderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the 15 last added links with their tags and users
     * @return Link[]
     */
    public function findLast15Links(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.tags', 't')
            ->addSelect('t')
            ->leftJoin('l.user', 'u')
            ->addSelect('u')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults(15)
            ->getQuery()
            ->getResult();
    }
}
