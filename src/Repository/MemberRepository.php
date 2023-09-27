<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 *
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository
{    
    /**
     * __construct
     *
     * @param  ManagerRegistry $registry Registry
     * @return void
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }
    
    /**
     * Save
     *
     * @param  Member $entity Entity
     * @param  bool   $flush  Flush
     * @return void
     */
    public function save(Member $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * Remove
     *
     * @param  Member $entity Entity
     * @param  bool   $flush  Flush
     * @return void
     */
    public function remove(Member $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * FindByUser
     *
     * @param  User $user User
     * @param  int $page  Page
     * @param  int $limit Limit
     * @return array
     */
    public function findByUser(User $user, $page, $limit): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.createdBy = :user')
            ->setParameter('user', $user)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * FindAllWithPagination
     *
     * @param  int $page  Page
     * @param  int $limit Limit
     * @return void
     */
    public function findAllWithPagination($page, $limit)
    {
        return $this->createQueryBuilder('m')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
