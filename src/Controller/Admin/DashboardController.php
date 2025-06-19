<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Country;
use App\Entity\Guide;
use App\Entity\Visitor;
use App\Entity\Visite;
use App\Entity\VisitorParticipation;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ChartBuilderInterface $chartBuilder,
        private \Doctrine\ORM\EntityManagerInterface $entityManager,
    ) {
    }

    public function index(): Response
    {
        $usersCount = $this->entityManager->getRepository(User::class)->count([]);
        $guidesCount = $this->entityManager->getRepository(Guide::class)->count([]);
        $visitorsCount = $this->entityManager->getRepository(Visitor::class)->count([]);
        $countriesCount = $this->entityManager->getRepository(Country::class)->count([]);
        $visitesCount = $this->entityManager->getRepository(Visite::class)->count([]);
        $participationsCount = $this->entityManager->getRepository(VisitorParticipation::class)->count([]);
        $participationsCount = $this->entityManager->getRepository(VisitorParticipation::class)->count([]);

        $data = [
            ['usersCount' => $usersCount, "crudControllerFqcn" => 'App\\Controller\\Admin\\UserCrudController', 
             'icon' => 'fas fa-users', "label" => 'Utilisateurs'],
            ['guidesCount' => $guidesCount, "crudControllerFqcn" => 'App\\Controller\\Admin\\GuideCrudController', 
             'icon' => 'fas fa-user-tie', "label" => 'Guides'],
            ['visitorsCount' => $visitorsCount, "crudControllerFqcn" => 'App\\Controller\\Admin\\VisitorCrudController', 
             'icon' => 'fas fa-user-friends', "label" => 'Visiteurs'],
            ['countriesCount' => $countriesCount, "crudControllerFqcn" => 'App\\Controller\\Admin\\CountryCrudController', 
             'icon' => 'fas fa-flag', "label" => 'Pays'],
            ['visitesCount' => $visitesCount, "crudControllerFqcn" => 'App\\Controller\\Admin\\VisiteCrudController', 
             'icon' => 'fas fa-map-marked-alt', "label" => 'Visites'],
            ['participationsCount' => $participationsCount, "crudControllerFqcn" => 'App\\Controller\\Admin\\VisitorParticipationCrudController', 
             'icon' => 'fas fa-list-alt', "label" => 'Participations'],
        ];

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai'],
            'datasets' => [[
                'label' => 'Visites par mois',
                'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                'borderColor' => 'rgb(54, 162, 235)',
                'data' => [10, 15, 8, 12, 20],
            ]],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ]);

        return $this->render('admin/page/my-dashboard.html.twig', [
            'data' => $data,
            'chart' => $chart,
        ]);

       }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('TravelParadiseAdmin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Guides', 'fas fa-user-tie', Guide::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        yield MenuItem::section('Gestion des visites');
        yield MenuItem::linkToCrud('Visites', 'fas fa-map-marker-alt', Visite::class);
        yield MenuItem::linkToCrud('Visiteurs', 'fas fa-user-friends', Visitor::class);
        yield MenuItem::linkToCrud('Participations', 'fas fa-list-alt', VisitorParticipation::class);
        yield MenuItem::section('Configuration');
        yield MenuItem::linkToCrud('Pays', 'fas fa-flag', Country::class);
        yield MenuItem::linkToRoute('Statistiques', 'fas fa-chart-bar', 'admin_stats');
    }
}
