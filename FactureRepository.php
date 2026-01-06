<?php

namespace App\Repository;

use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    /**
     * Recherche des factures selon différents critères
     *
     * @param string|null $statut   Statut de la facture (Payée, En attente, Annulée)
     * @param \DateTimeInterface|null $from Date de début
     * @param \DateTimeInterface|null $to   Date de fin
     * @param int|null $customerId  ID du client
     * @return Facture[]
     */
    public function search(?string $statut, ?\DateTimeInterface $from, ?\DateTimeInterface $to, ?int $customerId): array
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.customer', 'c')->addSelect('c');

        if ($statut) {
            $qb->andWhere('f.statut = :statut')
               ->setParameter('statut', $statut);
        }

        if ($from) {
            $qb->andWhere('f.sentAt >= :from')
               ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('f.sentAt <= :to')
               ->setParameter('to', $to);
        }

        if ($customerId) {
            $qb->andWhere('c.id = :customerId')
               ->setParameter('customerId', $customerId);
        }

        $qb->orderBy('f.sentAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne le montant total des factures par statut
     */
    public function getTotalByStatut(string $statut): float
    {
        return (float) $this->createQueryBuilder('f')
            ->select('SUM(f.montant)')
            ->andWhere('f.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
