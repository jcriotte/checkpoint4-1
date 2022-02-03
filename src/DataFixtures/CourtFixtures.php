<?php

namespace App\DataFixtures;

use App\Entity\Court;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourtFixtures extends Fixture
{
    private const COURTS = [
        [1, 'Court Philippe-Chatrier', 'clay', true],
        [2, 'Court Suzanne Lenglen', 'grass', false],
        [3, 'Court Simonne Mathieu', 'hard', true],
        [4, 'Court Central', 'clay', false],
        [5, 'Court extérieur n°5', 'hard', false]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::COURTS as $courtDetails) {
            $court = new Court();

            $court->setName($courtDetails[1]);

            $court->setSurface($courtDetails[2]);
            $court->setCover($courtDetails[3]);

            $manager->persist($court);

            $this->addReference("court_$courtDetails[0]", $court);
        }

        $manager->flush();
    }
}
