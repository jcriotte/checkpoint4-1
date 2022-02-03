<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Booking;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\CourtFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BookingFixtures extends Fixture implements DependentFixtureInterface
{
    private const BOOKINGS = 1000;
    private const OPENING_HOUR = 9;
    private const CLOSING_HOUR = 22;

    public function load(ObjectManager $manager): void
    {
        $minDate = strtotime("today");
        $maxDate = strtotime("22-02-27");

        $bookingRefs = [];

        for ($i = 0; $i < self::BOOKINGS; $i++) {
            $booking = new Booking();

            $userId = rand(0, 199);
            $courtId = rand(1, 5);

            $date = rand($minDate, $maxDate);
            $dateString = date("Y-m-d", $date);
            $dateTime = new DateTime($dateString);

            $hour = rand(self::OPENING_HOUR, self::CLOSING_HOUR);

            $bookingRef = "$courtId$date$hour";
            if (in_array($bookingRef, $bookingRefs)) {
                continue;
            }

            $bookingRefs[] = $bookingRef;

            $booking->setUser($this->getReference("user_$userId"));
            $booking->setCourt($this->getReference("court_$courtId"));

            $booking->setHour($hour);

            $booking->setDate($dateTime);

            $manager->persist($booking);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CourtFixtures::class
        ];
    }
}
