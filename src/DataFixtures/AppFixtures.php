<?php
namespace App\DataFixtures;

use App\Entity\Guide;
use App\Entity\Country;
use App\Entity\Visite;
use App\Entity\Visitor;
use App\Entity\VisitorParticipation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fssr_FR');

        // 1. Pays
        $countries = [];
        foreach (["France", "Italie", "Espagne"] as $countryName) {
            $country = new Country();
            $country->setName($countryName);
            $country->setCode(strtoupper(substr($countryName, 0, 2)));
            $manager->persist($country);
            $countries[] = $country;
        }

        // 2. Guides
        $guides = [];
        for ($i = 1; $i <= 3; $i++) {
            $guide = new Guide();
            $guide->setFirstName($faker->firstName);
            $guide->setLastName($faker->lastName);
            $guide->setEmail("guide$i@example.com");
            $guide->setPassword('$2y$13$abcdefghijklmnopqrstuv'); // fake hash
            $guide->setIsActive(true);
            $guide->setCountry($faker->randomElement($countries));
            $guide->setPhotoUrl(null);
            $manager->persist($guide);
            $guides[] = $guide;
        }

        // 3. Visiteurs
        $visitors = [];
        for ($i = 1; $i <= 15; $i++) {
            $visitor = new Visitor();
            $visitor->setFirstName($faker->firstName);
            $visitor->setLastName($faker->lastName);
            $visitor->setEmail("visiteur$i@example.com");
            $visitor->setIdentityNumber("123456");
            $manager->persist($visitor);
            $visitors[] = $visitor;
        }

        // 4. Visites
        $visites = [];
        for ($i = 1; $i <= 5; $i++) {
            $visite = new Visite();
            $visite->setPlaceToVisit($faker->city);
            $visite->setCountry($faker->randomElement($countries));
            $date = $faker->dateTimeBetween('-3 months', '+2 months');
            $visite->setVisitDate($date);
            $start = (clone $date)->setTime($faker->numberBetween(8, 15), 0);
            $visite->setStartTime($start);
            $duration = $faker->randomFloat(1, 1, 3);
            $visite->setDuration($duration);
            $end = (clone $start)->modify("+{$duration} hour");
            $visite->setEndTime($end);
            $visite->setAssignedGuide($faker->randomElement($guides));
            $visite->setVisitComment($faker->sentence);
            $visite->setStatus('scheduled');
            $manager->persist($visite);
            $visites[] = $visite;
        }

        // 5. Participations
        foreach ($visites as $visite) {
            $nb = $faker->numberBetween(5, 12);
            $selectedVisitors = $faker->randomElements($visitors, $nb);
            foreach ($selectedVisitors as $visitor) {
                $participation = new VisitorParticipation();
                $participation->setVisite($visite);
                $participation->setVisitor($visitor);
                $participation->setPresent($faker->boolean(80));
                $participation->setComment($faker->optional()->sentence);
                $manager->persist($participation);
            }
        }

        $manager->flush();
    }
} 