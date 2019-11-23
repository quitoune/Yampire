<?php

namespace App\Repository;

use App\Entity\Quizz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Quizz|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quizz|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quizz[]    findAll()
 * @method Quizz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuizzRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quizz::class);
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
    
    /**
     * Génération de la requête
     *
     * @param QueryBuilder $query
     * @param array $params
     *            [order] => ordre de tri
     *            [page] => page (pagination)
     *            [search] => tableau contenant les éléments de la recherche
     *            [repository] => repository (objet courant)
     *            [field] => champ de tri,
     *            [condition] => tableau contenant des conditions supplémentaires en dehors des filtres de l'utilisateur
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function generateParamsSql(QueryBuilder $query, array $params)
    {
        $index = 1;
        if (isset($params['search'])) {
            foreach ($params['search'] as $searchKey => $valueKey) {
                $explode_key = explode('-', $searchKey);
                if (count($explode_key) == 3) {
                    $query = $query->join($explode_key[0] . '.' . $explode_key[1], $explode_key[1]);
                    $query->andWhere($explode_key[1] . "." . $explode_key[2] . " LIKE :searchTerm$index");
                    $query->setParameter('searchTerm' . $index, '%' . $valueKey . '%');
                } else {
                    $query->andWhere(str_replace('-', '.', $searchKey) . " LIKE :searchTerm$index");
                    $query->setParameter('searchTerm' . $index, '%' . $valueKey . '%');
                }
                $index ++;
            }
        }
        if (isset($params['jointure'])) {
            foreach ($params['jointure'] as $jointure) {
                $query->join($jointure['oldrepository'] . '.' . $jointure['newrepository'], $jointure['newrepository']);
            }
        }
        if (isset($params['condition'])) {
            if (is_array($params['condition'])) {
                foreach ($params['condition'] as $condition) {
                    $query->andWhere($condition);
                }
            } else {
                $query->andWhere($params['condition']);
            }
        }
        return $query;
    }
}
