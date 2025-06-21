<?php

namespace App\Repository;

use App\Entity\Visite;
use App\Entity\Guide;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visite>
 */
class VisiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visite::class);
    }

//    /**
//     * @return Visite[] Returns an array of Visite objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Visite
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return Visite[]
     */
    public function findUpcomingByGuide(Guide $guide): array
    {
        $now = new \DateTimeImmutable();
        return $this->createQueryBuilder('v')
            ->andWhere('v.assignedGuide = :guide')
            ->andWhere('v.visitDate > :today OR (v.visitDate = :today AND v.startTime > :nowTime)')
            ->setParameter('guide', $guide)
            ->setParameter('today', $now->format('Y-m-d'))
            ->setParameter('nowTime', $now->format('H:i:s'))
            ->orderBy('v.visitDate', 'ASC')
            ->addOrderBy('v.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Visite[]
     */
    public function findOngoingByGuide(Guide $guide): array
    {
        $now = new \DateTimeImmutable();
        return $this->createQueryBuilder('v')
            ->andWhere('v.assignedGuide = :guide')
            ->andWhere('v.visitDate = :today')
            ->andWhere('v.startTime <= :nowTime AND v.endTime >= :nowTime')
            ->setParameter('guide', $guide)
            ->setParameter('today', $now->format('Y-m-d'))
            ->setParameter('nowTime', $now->format('H:i:s'))
            ->orderBy('v.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Visite[]
     */
    public function findPastByGuide(Guide $guide): array
    {
        $now = new \DateTimeImmutable();
        return $this->createQueryBuilder('v')
            ->andWhere('v.assignedGuide = :guide')
            ->andWhere('v.visitDate < :today OR (v.visitDate = :today AND v.endTime < :nowTime)')
            ->setParameter('guide', $guide)
            ->setParameter('today', $now->format('Y-m-d'))
            ->setParameter('nowTime', $now->format('H:i:s'))
            ->orderBy('v.visitDate', 'DESC')
            ->addOrderBy('v.startTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByMonthForGuide(Guide $guide): array
    {
        $visites = $this->createQueryBuilder('v')
            ->where('v.assignedGuide = :guide')
            ->setParameter('guide', $guide)
            ->getQuery()
            ->getResult();
        $stats = [];
        foreach ($visites as $v) {
            $date = $v->getVisitDate();
            if ($date) {
                $key = $date->format('Y-m');
                $stats[$key] = ($stats[$key] ?? 0) + 1;
            }
        }
        ksort($stats);
        return $stats;
    }

    public function countByCountryForGuide(Guide $guide): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('c.name as country, COUNT(v.id) as nb')
            ->join('v.country', 'c')
            ->where('v.assignedGuide = :guide')
            ->setParameter('guide', $guide)
            ->groupBy('c.name')
            ->orderBy('nb', 'DESC');
        $results = $qb->getQuery()->getResult();
        $stats = [];
        foreach ($results as $row) {
            $stats[$row['country']] = (int) $row['nb'];
        }
        return $stats;
    }

    public function countByMonth(): array
    {
        $visites = $this->findAll();
        $stats = [];
        foreach ($visites as $v) {
            $date = $v->getVisitDate();
            if ($date) {
                $key = $date->format('Y-m');
                $stats[$key] = ($stats[$key] ?? 0) + 1;
            }
        }
        ksort($stats);
        return $stats;
    }

    public function countByMonthAndGuide(): array
    {
        $visites = $this->findAll();
        $stats = [];
        foreach ($visites as $v) {
            $date = $v->getVisitDate();
            $guide = $v->getAssignedGuide();
            if ($date && $guide) {
                $key = $date->format('Y-m');
                $guideName = $guide->getFirstName() . ' ' . $guide->getLastName();
                $stats[$key][$guideName] = ($stats[$key][$guideName] ?? 0) + 1;
            }
        }
        // Format pour le template : [{month, guide, nb}, ...]
        $result = [];
        foreach ($stats as $month => $guides) {
            foreach ($guides as $guide => $nb) {
                $result[] = ['month' => $month, 'guide' => $guide, 'nb' => $nb];
            }
        }
        usort($result, fn($a, $b) => $a['month'] <=> $b['month'] ?: $a['guide'] <=> $b['guide']);
        return $result;
    }

    public function countByCountry(): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('c.name as country, COUNT(v.id) as nb')
            ->join('v.country', 'c')
            ->groupBy('c.name')
            ->orderBy('nb', 'DESC');
        $results = $qb->getQuery()->getResult();
        $stats = [];
        foreach ($results as $row) {
            $stats[$row['country']] = (int) $row['nb'];
        }
        return $stats;
    }

    public function countByGuide(): array
    {
        $visites = $this->findAll();
        $stats = [];
        foreach ($visites as $v) {
            $guide = $v->getAssignedGuide();
            if ($guide) {
                $guideName = $guide->getFirstName() . ' ' . $guide->getLastName();
                $stats[$guideName] = ($stats[$guideName] ?? 0) + 1;
            }
        }
        $result = [];
        foreach ($stats as $guide => $nb) {
            $result[] = ['guide' => $guide, 'nb' => $nb];
        }
        usort($result, fn($a, $b) => $b['nb'] <=> $a['nb']);
        return $result;
    }

    public function countByVisite(): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('v.id, v.placeToVisit, COUNT(vp.id) as nb')
            ->leftJoin('v.visitorParticipations', 'vp')
            ->groupBy('v.id, v.placeToVisit')
            ->orderBy('nb', 'DESC');
        return $qb->getQuery()->getResult();
    }

    public function countByVisiteForGuide(Guide $guide): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('v.id, v.placeToVisit, COUNT(vp.id) as nb')
            ->leftJoin('v.visitorParticipations', 'vp')
            ->where('v.assignedGuide = :guide')
            ->setParameter('guide', $guide)
            ->groupBy('v.id, v.placeToVisit')
            ->orderBy('nb', 'DESC');
        return $qb->getQuery()->getResult();
    }
}
