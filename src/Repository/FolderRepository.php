<?php

namespace App\Repository;

use App\Entity\Folder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Folder>
 *
 * @method Folder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Folder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Folder[]    findAll()
 * @method Folder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolderRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Folder::class);
            $this->entityManager = $entityManager;

    }

    public function save(Folder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Folder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Folder[] Returns an array of Folder objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Folder
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

public function getChildrenFolders($parentFolder)
    {
        $children = $parentFolder->getFolders();
        return $children;
    }
    
public function deleteFolderAndChildren($folder)
    {
        // marquer le dossier courant comme supprimé
        $folder->setIsDeleted(true);
        
        // parcourir tous les dossiers enfants
        foreach ($folder->getFolders() as $child) {
            // appeler récursivement la fonction pour chaque dossier enfant
            $this->deleteFolderAndChildren($child);

        }
        foreach ($folder->getDocuments() as $doc) {
            // appeler récursivement la fonction pour chaque dossier enfant
            $doc->setIsDeleted(true);

        }
        
        // enregistrer les modifications
        $this->entityManager->flush();
    }

    public function restoreFolderAndChildren(Folder $folder): void
    {
        $folder->setIsDeleted(false);
        // parcourir tous les dossiers enfants
        foreach ($folder->getFolders() as $child) {
            // appeler récursivement la fonction pour chaque dossier enfant
            $this->restoreFolderAndChildren($child);
        }
        foreach ($folder->getDocuments() as $doc) {
            // appeler récursivement la fonction pour chaque dossier enfant
            $doc->setIsDeleted(false);

        }

       // enregistrer les modifications
       $this->entityManager->flush();
    }
}


