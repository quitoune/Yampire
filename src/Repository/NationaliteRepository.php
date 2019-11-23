<?php

namespace App\Repository;

use App\Entity\Nationalite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Nationalite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nationalite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nationalite[]    findAll()
 * @method Nationalite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NationaliteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nationalite::class);
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
        
        $qb = $this->createQueryBuilder('n')
        ->orderBy('n.id')
        ->setFirstResult( $first )
        ->setMaxResults( $max );
        
        $query = $qb->getQuery();
        
        $paginator = new Paginator($query);
        
        $nb = $this->createQueryBuilder('n')
        ->select('count(n.id)')
        ->getQuery()
        ->getSingleScalarResult();
        
        if ( ($paginator->count() <= $first) && $page != 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas.'); // page 404, sauf pour la première page
        }
        
        return array('paginator' => $paginator, 'nombre' => $nb);
    }
}
