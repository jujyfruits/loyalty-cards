<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CardRepository extends EntityRepository {

    public function getMaxNumberOfSeries($series) {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('c.number')
                ->from('AppBundle\Entity\Card', 'c')
                ->where('c.series = :series')
                ->setParameter('series', $series)
                ->orderBy('c.number', 'DESC')
                ->setMaxResults(1);

        $query = $qb->getQuery();
        return $query->getOneOrNullResult();
        ;
    }

    public function findCardPurchases($cardId) {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('p')
                ->from('AppBundle\Entity\Purchase', 'p')
                ->leftJoin('p.card', 'c')
                ->where('c.id = :cardId')
                ->setParameter('cardId', $cardId);

        /*
          $qb
          ->select('Project.id')
          ->from('AppBundle\Entity\Project', 'Project')
          ->leftJoin('Project.user', 'User')
          ->where('User.id = :id')
          ->setParameter('id', $id) */

        $query = $qb->getQuery();
        return $query->getResult();
        ;
    }

}
