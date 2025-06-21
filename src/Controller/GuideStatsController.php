<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\VisiteRepository;
use App\Repository\VisitorParticipationRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class GuideStatsController extends AbstractController
{
    /**
     * @Route("/guide/statistiques", name="guide_stats")
     */
    public function index(VisiteRepository $visiteRepository, VisitorParticipationRepository $participationRepository, UserInterface $user): Response
    {
        // Nombre total de visites réalisées
        $visites = $visiteRepository->findBy(['assignedGuide' => $user]);
        $nbVisites = count($visites);

        // Nombre total de visiteurs accompagnés
        $nbVisiteurs = $participationRepository->countByGuide($user);

        // Taux de présence moyen
        $presenceStats = $participationRepository->presenceStatsByGuide($user);
        $tauxPresence = $presenceStats['total'] > 0 ? round(100 * $presenceStats['present'] / $presenceStats['total'], 1) : 0;

        // Statistiques par mois
        $statsMois = $visiteRepository->countByMonthForGuide($user);
        // Statistiques par pays
        $statsPays = $visiteRepository->countByCountryForGuide($user);

        return $this->render('guide/stats.html.twig', [
            'nbVisites' => $nbVisites,
            'nbVisiteurs' => $nbVisiteurs,
            'tauxPresence' => $tauxPresence,
            'statsMois' => $statsMois,
            'statsPays' => $statsPays,
        ]);
    }
} 