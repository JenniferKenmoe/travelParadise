<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Country;
use App\Repository\CountryRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GuideProfileController extends AbstractController
{
    /**
     * @Route("/guide/profil", name="guide_profile")
     */
    public function index(Request $request, EntityManagerInterface $em, CountryRepository $countryRepo, UserPasswordHasherInterface $passwordHasher): Response
    {
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
            $this->addFlash('success', 'Profil mis à jour !');
            return $this->redirectToRoute('guide_profile');
        }
        return $this->render('guide/profile.html.twig', [
            'guide' => $guide,
            'countries' => $countries,
        ]);
    }
} 