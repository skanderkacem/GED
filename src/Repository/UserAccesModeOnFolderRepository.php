<?php

namespace App\Repository;

use App\Entity\UserAccesModeOnFolder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserAccesModeOnFolder>
 *
 * @method UserAccesModeOnFolder|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAccesModeOnFolder|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAccesModeOnFolder[]    findAll()
 * @method UserAccesModeOnFolder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAccesModeOnFolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAccesModeOnFolder::class);
    }

    public function save(UserAccesModeOnFolder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserAccesModeOnFolder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return UserAccesModeOnFolder[] Returns an array of UserAccesModeOnFolder objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserAccesModeOnFolder
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
