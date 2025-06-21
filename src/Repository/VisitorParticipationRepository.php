<?php

namespace App\Repository;

use App\Entity\VisitorParticipation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Guide;

/**
 * @extends ServiceEntityRepository<VisitorParticipation>
 */
class VisitorParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VisitorParticipation::class);
    }

//    /**
//     * @return VisitorParticipation[] Returns an array of VisitorParticipation objects
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

//    public function findOneBySomeField($value): ?VisitorParticipation
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function countByGuide(Guide $guide): int
    {
        return $this->createQueryBuilder('vp')
            ->select('COUNT(vp.id)')
            ->join('vp.visite', 'v')
            ->where('v.assignedGuide = :guide')
            ->setParameter('guide', $guide)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function presenceStatsByGuide(Guide $guide): array
    {
        $qb = $this->createQueryBuilder('vp')
            ->select('COUNT(vp.id) as total, SUM(CASE WHEN vp.present = true THEN 1 ELSE 0 END) as present')
            ->join('vp.visite', 'v')
            ->where('v.assignedGuide = :guide')
            ->setParameter('guide', $guide);
        $result = $qb->getQuery()->getSingleResult();
        return [
            'total' => (int) $result['total'],
            'present' => (int) $result['present'],
        ];
    }

    public function presenceRateByMonth(): array
    {
        $participations = $this->findAll();
        $stats = [];
        foreach ($participations as $p) {
            $visite = $p->getVisite();
            if ($visite && $visite->getVisitDate()) {
                $key = $visite->getVisitDate()->format('Y-m');
                if (!isset($stats[$key])) {
                    $stats[$key] = ['total' => 0, 'present' => 0];
                }
                $stats[$key]['total']++;
                if ($p->isPresent()) {
                    $stats[$key]['present']++;
                }
            }
        }
        $result = [];
        foreach ($stats as $month => $data) {
            $result[$month] = $data['total'] > 0 ? round(100 * $data['present'] / $data['total'], 1) : 0;
        }
        ksort($result);
        return $result;
    }

    public function presenceRateByMonthForGuide(Guide $guide): array
    {
        $participations = $this->createQueryBuilder('vp')
            ->join('vp.visite', 'v')
            ->where('v.assignedGuide = :guide')
            ->setParameter('guide', $guide)
            ->getQuery()
            ->getResult();
        $stats = [];
        foreach ($participations as $p) {
            $visite = $p->getVisite();
            if ($visite && $visite->getVisitDate()) {
                $key = $visite->getVisitDate()->format('Y-m');
                if (!isset($stats[$key])) {
                    $stats[$key] = ['total' => 0, 'present' => 0];
                }
                $stats[$key]['total']++;
                if ($p->isPresent()) {
                    $stats[$key]['present']++;
                }
            }
        }
        $result = [];
        foreach ($stats as $month => $data) {
            $result[$month] = $data['total'] > 0 ? round(100 * $data['present'] / $data['total'], 1) : 0;
        }
        ksort($result);
        return $result;
    }
}
