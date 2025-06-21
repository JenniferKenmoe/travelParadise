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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
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

        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        $data = [
            [
                'count' => $usersCount,
                'url' => $adminUrlGenerator->setController(\App\Controller\Admin\UserCrudController::class)->generateUrl(),
                'icon' => 'fas fa-users',
                'label' => 'Utilisateurs'
            ],
            [
                'count' => $guidesCount,
                'url' => $adminUrlGenerator->setController(\App\Controller\Admin\GuideCrudController::class)->generateUrl(),
                'icon' => 'fas fa-user-tie',
                'label' => 'Guides'
            ],
            [
                'count' => $visitorsCount,
                'url' => $adminUrlGenerator->setController(\App\Controller\Admin\VisitorCrudController::class)->generateUrl(),
                'icon' => 'fas fa-user-friends',
                'label' => 'Visiteurs'
            ],
            [
                'count' => $countriesCount,
                'url' => $adminUrlGenerator->setController(\App\Controller\Admin\CountryCrudController::class)->generateUrl(),
                'icon' => 'fas fa-flag',
                'label' => 'Pays'
            ],
            [
                'count' => $visitesCount,
                'url' => $adminUrlGenerator->setController(\App\Controller\Admin\VisiteCrudController::class)->generateUrl(),
                'icon' => 'fas fa-map-marked-alt',
                'label' => 'Visites'
            ],
            [
                'count' => $participationsCount,
                'url' => $adminUrlGenerator->setController(\App\Controller\Admin\VisitorParticipationCrudController::class)->generateUrl(),
                'icon' => 'fas fa-list-alt',
                'label' => 'Participations'
            ],
        ];

        return $this->render('admin/page/my-dashboard.html.twig', [
            'data' => $data,
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
        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToCrud('Guides', 'fas fa-user-tie', Guide::class);
            yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
            yield MenuItem::section('Gestion des visites');
            yield MenuItem::linkToCrud('Visites', 'fas fa-map-marker-alt', Visite::class);
            yield MenuItem::linkToCrud('Visiteurs', 'fas fa-user-friends', Visitor::class);
            yield MenuItem::linkToCrud('Participations', 'fas fa-list-alt', VisitorParticipation::class);
            yield MenuItem::section('Configuration');
            yield MenuItem::linkToCrud('Pays', 'fas fa-flag', Country::class);
            yield MenuItem::linkToRoute('Statistiques', 'fas fa-chart-bar', 'admin_stats');
        } else {
            yield MenuItem::linkToCrud('Guides', 'fas fa-user-tie', Guide::class);
            yield MenuItem::section('Gestion des visites');
            yield MenuItem::linkToCrud('Visites', 'fas fa-map-marker-alt', Visite::class);
            yield MenuItem::linkToCrud('Participations', 'fas fa-list-alt', VisitorParticipation::class);
        }
    }
}
