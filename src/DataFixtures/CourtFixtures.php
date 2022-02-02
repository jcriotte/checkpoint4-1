<?php

namespace App\DataFixtures;

use App\Entity\Court;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourtFixtures extends Fixture
{
    private const COURTS = [
        [1, 'clay', true],
        [2, 'green', false],
        [3, 'hard', true],
        [4, 'clay', false],
        [5, 'hard', false]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::COURTS as $courtDetails) {
            $court = new Court();

            $courtNumber  = sprintf("%02d", $courtDetails[0]);
            $court->setName("Court n°$courtNumber");

            $court->setSurface($courtDetails[1]);
            $court->setCover($courtDetails[2]);

            $manager->persist($court);

            $this->addReference("court_$courtDetails[0]", $court);
        }

        $manager->flush();
    }
}
