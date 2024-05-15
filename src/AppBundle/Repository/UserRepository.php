<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UserRepository
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getUsersQuery()
    {
        return $this->createQueryBuilder('user');
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Liste paginée des utilisateurs filtrés par profil
     * @param $page
     * @param $nbPerPage
     * @return Paginator
     */
    public function getPaginatedUsers($page, $nbPerPage)
    {


        $query = $this
            ->getUsersQuery()
            ->orderBy('user.id', 'DESC')


            ->getQuery()
        ;

        $query
            ->setFirstResult(($page - 1) * $nbPerPage)
            ->setMaxResults($nbPerPage)
        ;

        return new Paginator($query, false);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Retrouve l'ensemble des informations d'un utilisateur par son ID
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserById($id)
    {
        return $this
            ->getUsersQuery()

            ->andWhere('user.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->getSingleResult()
            ;
    }

}
