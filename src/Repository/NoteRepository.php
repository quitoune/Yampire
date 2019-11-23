<?php

namespace App\Repository;

use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Note|null find($id, $lockMode = null, $lockVersion = null)
 * @method Note|null findOneBy(array $criteria, array $orderBy = null)
 * @method Note[]    findAll()
 * @method Note[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     *
     * @param int $page
     * @param int $max
     * @params array $params
     * @throws NotFoundHttpException
     * @return \Doctrine\ORM\Tools\Pagination\Paginator[]|int[]
     */
    public function findAllElements(int $page, int $max, array $params = array())
    {
        if ($page < 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas');
        }
        
        $first = ($page - 1) * $max;
        
        // pagination
        $query = $this->createQueryBuilder($params['repository'])->setFirstResult($first);
        
        // Génération des paramètres SQL
        $query = $this->generateParamsSql($query, $params);
        $query->orderBy($params['field'], $params['order'])->setMaxResults($max);
        
        $paginator = new Paginator($query);
        
        // Nombre total de personnage
        $query = $this->createQueryBuilder($params['repository'])->select('COUNT(' . $params['repository'] . '.id)');
        // Génération des paramètres SQL
        
        $query = $this->generateParamsSql($query, $params);
        $nb = $query->getQuery()->getSingleScalarResult();
        
        if (($paginator->count() <= $first) && $page != 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas.'); // page 404, sauf pour la première page
        }
        
        return array(
            'paginator' => $paginator,
            'nombre' => $nb
        );
    }
}
