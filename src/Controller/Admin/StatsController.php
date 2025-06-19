<?php
namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatsController extends AbstractController
{
    #[Route('/admin/stats', name: 'admin_stats')]
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        // nombre de visiteurs par visite
        $data = [
            'labels' => ['Visite 1', 'Visite 2', 'Visite 3'],
            'datasets' => [[
                'label' => 'Nombre de visiteurs',
                'backgroundColor' => 'rgb(54, 162, 235)',
                'data' => [12, 19, 3],
            ]],
        ];

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData($data);

        return $this->render('admin/page/stats.html.twig', [
            'chart' => $chart,
        ]);
    }
}