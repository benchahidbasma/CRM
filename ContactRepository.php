<?php

namespace App\Repository;

use App\Entity\Contact;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Contact::class); }

    public function searchForUser(?string $q, ?string $city, ?string $tagLabel, User $user, bool $isAdmin, string $sort = 'name', string $dir = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.tags', 't')->addSelect('t');

        if (!$isAdmin) {
            $qb->andWhere('c.owner = :owner')->setParameter('owner', $user);
        }

        if ($q) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q OR c.phone LIKE :q OR c.notes LIKE :q')
               ->setParameter('q', "%$q%");
        }

        if ($city) {
            $qb->andWhere('c.city LIKE :city')->setParameter('city', "%$city%");
        }

        if ($tagLabel) {
            $qb->andWhere('t.label LIKE :tag')->setParameter('tag', "%$tagLabel%");
        }

        $allowedSort = ['name','city','created_at'];
        $allowedDir = ['ASC','DESC'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'name';
        if (!in_array($dir, $allowedDir, true)) $dir = 'ASC';

        $qb->orderBy('c.'.$sort, $dir);

        return $qb->getQuery()->getResult();
    }
}
