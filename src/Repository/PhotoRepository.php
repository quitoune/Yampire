<?php

namespace App\Repository;

use App\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Photo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Photo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Photo[]    findAll()
 * @method Photo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    /**
     *
     * @param int $page
     * @param int $max
     * @throws NotFoundHttpException
     * @return \Doctrine\ORM\Tools\Pagination\Paginator[]|mixed[]|\Doctrine\DBAL\Driver\Statement[]|array[]|NULL[]
     */
    public function findAllElements(int $page, int $max)
    {
        if ($page < 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas');
        }
        
        $first = ($page -1)*$max;
        
        $qb = $this->createQueryBuilder('p')
        ->orderBy('p.id')
        ->setFirstResult( $first )
        ->setMaxResults( $max );
        
        $query = $qb->getQuery();
        
        $paginator = new Paginator($query);
        
        $nb = $this->createQueryBuilder('p')
        ->select('count(p.id)')
        ->getQuery()
        ->getSingleScalarResult();
        
        if ( ($paginator->count() <= $first) && $page != 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas.'); // page 404, sauf pour la première page
        }
        
        return array('paginator' => $paginator, 'nombre' => $nb);
    }
}
