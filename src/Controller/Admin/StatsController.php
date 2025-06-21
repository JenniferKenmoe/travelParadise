<?php
namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\VisiteRepository;
use App\Repository\VisitorParticipationRepository;

class StatsController extends AbstractController
{
    #[Route('/admin/stats', name: 'admin_stats')]
    public function index(VisiteRepository $visiteRepo, VisitorParticipationRepository $vpRepo): Response
    {
        // 1. Nombre de visites par mois
        $visitesParMois = $visiteRepo->countByMonth();
        // 2. Nombre de visites par mois et par guide
        $visitesParMoisGuide = $visiteRepo->countByMonthAndGuide();
        // 3. Taux de présence des touristes par mois
        $tauxPresenceMois = $vpRepo->presenceRateByMonth();
        // 4. Stats par guide
        $parGuide = $visiteRepo->countByGuide();
        // 5. Stats par pays
        $parPays = $visiteRepo->countByCountry();
        // 6. Stats par visite (nombre de visiteurs)
        $parVisite = $visiteRepo->countByVisite();

        return $this->render('admin/page/stats.html.twig', [
            'visitesParMois' => $visitesParMois,
            'visitesParMoisGuide' => $visitesParMoisGuide,
            'tauxPresenceMois' => $tauxPresenceMois,
            'parGuide' => $parGuide,
            'parPays' => $parPays,
            'parVisite' => $parVisite,
        ]);
    }
}