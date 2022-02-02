<?php

namespace App\Service;

use App\Repository\CourtRepository;
use App\Repository\BookingRepository;

class BookingInterface
{
    private const HOUR_OPENING = 9;
    private const HOUR_CLOSING = 23;

    private BookingRepository $bookingRepository;
    private CalendarInterface $calendarInterface;

    private array $courts;
    private array $courtsId;
    private array $courtsTemplate;

    public function __construct(
        BookingRepository $bookingRepository,
        CourtRepository $courtRepository,
        CalendarInterface $calendarInterface
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->calendarInterface = $calendarInterface;

        $this->courts = $courtRepository->findAllWithoutBookings();
        $this->courtsId = $this->getCourtsId();
        $this->courtsTemplate = $this->generateTemplateCourts();
    }

    public function getBookingsPerCourtAndDate(
        int $day,
        int $month,
        int $year,
        bool $details
    ): array {
        $courts = $this->courtsTemplate;

        $bookings = $this->getBookingsPerDate($day, $month, $year);

        foreach ($bookings as $booking) {
            $this->addBookingInfosToSlots($details, $courts, $booking);
        }

        return $courts;
    }

    private function addBookingInfosToSlots(
        bool $details,
        array &$courts,
        array $booking
    ): void {
        $courtId = $booking['court_id'];
        $hour = $booking['hour'];

        if (false === $details) {
            if (in_array($courtId, $this->courtsId)) {
                $courts[$courtId]['slots'][$hour]['booked'] = true;
            }
        } else {
            if (in_array($courtId, $this->courtsId)) {
                $courts[$courtId]['slots'][$hour]['booked'] = true;

                $courts[$courtId]['slots'][$hour]['user_id'] = $booking['user_id'];
                $courts[$courtId]['slots'][$hour]['user_pseudo'] = $booking['user_pseudo'];
                $courts[$courtId]['slots'][$hour]['user_email'] = $booking['user_email'];
            }
        }
    }

    private function getCourtsId(): array
    {
        $courtsId = [];

        foreach ($this->courts as $court) {
            $id = $court['id'];

            $courtsId[] = $id;
        }

        return $courtsId;
    }

    private function getBookingsPerDate(int $day, int $month, int $year): array
    {
        $dateQuery = $this->calendarInterface->formatDateQuery($day, $month, $year);
        $bookings = $this->bookingRepository->findByDateWithoutBookings($dateQuery);

        return $bookings;
    }

    private function generateTemplateCourts(): array
    {
        $courts = [];

        foreach ($this->courts as $court) {
            $id = $court['id'];

            $this->setFrenchSurfacesNames($court);

            $courts[$id]['info'] = $court;
            $courts[$id]['slots'] = $this->generateTemplateBookings();
        }

        return $courts;
    }

    private function generateTemplateBookings(): array
    {
        $bookings = [];
        for ($i = self::HOUR_OPENING; $i < self::HOUR_CLOSING; $i++) {
            $bookings[$i] = ['booked' => false];
        }

        return $bookings;
    }

    private function setFrenchSurfacesNames(array &$court): void
    {
        $helper = [
            'clay' => 'Terre Battue',
            'grass' => 'Gazon',
            'hard' => 'Bitume'
        ];

        $court['surfaceFR'] = $helper[$court['surface']];
    }
}
