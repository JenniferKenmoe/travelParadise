<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\VisiteRepository;
use App\Repository\VisitorParticipationRepository;
use App\Repository\CountryRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Visite;
use App\Entity\Country;

final class GuideController extends AbstractController
{
    #[Route('/guide', name: 'app_guide')]
    public function index(): Response
    {
        return $this->render('guide/index.html.twig', [
            'controller_name' => 'GuideController',
        ]);
    }

    #[Route('/guide/login', name: 'guide_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('guide/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/guide/dashboard', name: 'guide_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('guide/dashboard.html.twig');
    }

    #[Route('/guide/statistiques', name: 'guide_stats')]
    public function stats(
        VisiteRepository $visiteRepository,
        VisitorParticipationRepository $participationRepository,
        UserInterface $user
    ): Response {
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
        // Taux de présence par mois
        $tauxPresenceMois = $participationRepository->presenceRateByMonthForGuide($user);
        // Alias pour compatibilité template
        $visitesParMois = $statsMois;
        // Nombre de visiteurs par visite (pour le guide)
        $parVisite = $visiteRepository->countByVisiteForGuide($user);

        return $this->render('guide/stats.html.twig', [
            'nbVisites' => $nbVisites,
            'nbVisiteurs' => $nbVisiteurs,
            'tauxPresence' => $tauxPresence,
            'statsMois' => $statsMois,
            'statsPays' => $statsPays,
            'tauxPresenceMois' => $tauxPresenceMois,
            'visitesParMois' => $visitesParMois,
            'parVisite' => $parVisite,
        ]);
    }

    #[Route('/guide/visites', name: 'guide_visites')]
    public function visites(VisiteRepository $visiteRepository, UserInterface $user): Response
    {
        $upcoming = $visiteRepository->findUpcomingByGuide($user);
        $ongoing = $visiteRepository->findOngoingByGuide($user);
        $past = $visiteRepository->findPastByGuide($user);
        return $this->render('guide/visites.html.twig', [
            'upcoming' => $upcoming,
            'ongoing' => $ongoing,
            'past' => $past,
        ]);
    }

    #[Route('/guide/visite/{id}', name: 'guide_visite_detail')]
    public function visiteDetail(
        Visite $visite,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $now = new \DateTimeImmutable();
        $isOngoing = $visite->getVisitDate() && $visite->getVisitDate()->format('Y-m-d') === $now->format('Y-m-d')
            && $visite->getStartTime() <= $now && $visite->getEndTime() >= $now;

        if ($isOngoing && $request->isMethod('POST')) {
            if ($request->request->has('finish_visit')) {
                $visite->setStatus('finished');
                $em->flush();
                $this->addFlash('success', 'La visite a été marquée comme terminée.');
                return $this->redirectToRoute('guide_visite_detail', ['id' => $visite->getId()]);
            }
            // Mettre à jour la présence et les commentaires
            foreach ($visite->getVisitorParticipations() as $participation) {
                $present = $request->request->get('present_' . $participation->getId(), null);
                $comment = $request->request->get('comment_' . $participation->getId(), null);
                $participation->setPresent($present === 'on');
                $participation->setComment($comment);
            }
            $globalComment = $request->request->get('visitComment', null);
            $visite->setVisitComment($globalComment);
            $em->flush();
            $this->addFlash('success', 'Feuille de présence enregistrée !');
            return $this->redirectToRoute('guide_visite_detail', ['id' => $visite->getId()]);
        }

        // Statistiques de présence/absence pour une visite
        $total = 0;
        $present = 0;
        foreach ($visite->getVisitorParticipations() as $participation) {
            $total++;
            if ($participation->isPresent()) {
                $present++;
            }
        }
        $absent = $total - $present;
        $tauxPresence = $total > 0 ? round(100 * $present / $total, 1) : 0;
        $tauxAbsence = $total > 0 ? round(100 * $absent / $total, 1) : 0;

        return $this->render('guide/visite_detail.html.twig', [
            'visite' => $visite,
            'isOngoing' => $isOngoing,
            'tauxPresence' => $tauxPresence,
            'tauxAbsence' => $tauxAbsence,
            'present' => $present,
            'absent' => $absent,
            'total' => $total,
        ]);
    }

    #[Route('/guide/profil', name: 'guide_profile')]
    public function profile(
        Request $request,
        EntityManagerInterface $em,
        CountryRepository $countryRepo,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $guide = $this->getUser();
        if (!$guide instanceof \App\Entity\Guide) {
            throw $this->createAccessDeniedException('Seuls les guides peuvent accéder à cette page.');
        }
        $countries = $countryRepo->findAll();
        if ($request->isMethod('POST')) {
            $guide->setFirstName($request->request->get('firstName'));
            $guide->setLastName($request->request->get('lastName'));
            $guide->setEmail($request->request->get('email'));
            $countryId = $request->request->get('country');
            $country = $countryId ? $countryRepo->find($countryId) : null;
            $guide->setCountry($country);
            if ($request->files->get('photo')) {
                $guide->setImageFile($request->files->get('photo'));
            }
            $newPassword = $request->request->get('newPassword');
            $confirmPassword = $request->request->get('confirmPassword');
            if ($newPassword || $confirmPassword) {
                if ($newPassword === $confirmPassword && strlen($newPassword) >= 6) {
                    $hashed = $passwordHasher->hashPassword($guide, $newPassword);
                    $guide->setPassword($hashed);
                    $this->addFlash('success', 'Mot de passe modifié avec succès.');
                } else {
                    $this->addFlash('danger', 'Les mots de passe ne correspondent pas ou sont trop courts (min 6 caractères).');
                    return $this->redirectToRoute('guide_profile');
                }
            }
            $em->flush();
            $guide->setImageFile(null); 
            $this->addFlash('success', 'Profil mis à jour !');
            return $this->redirectToRoute('guide_profile');
        }
        return $this->render('guide/profile.html.twig', [
            'guide' => $guide,
            'countries' => $countries,
        ]);
    }
}
