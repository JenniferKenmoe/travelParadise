<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\VisiteRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Visite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class GuideVisiteController extends AbstractController
{
    /**
     * @Route("/guide/visites", name="guide_visites")
     */
    public function index(VisiteRepository $visiteRepository, UserInterface $user): Response
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

    /**
     * @Route("/guide/visite/{id}", name="guide_visite_detail")
     */
    public function detail(Visite $visite, Request $request, EntityManagerInterface $em): Response
    {
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

        return $this->render('guide/visite_detail.html.twig', [
            'visite' => $visite,
            'isOngoing' => $isOngoing,
        ]);
    }
} 