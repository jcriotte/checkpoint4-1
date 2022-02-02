<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Booking;
use App\Service\BookingInterface;
use App\Service\CalendarInterface;
use App\Repository\CourtRepository;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/booking", name="booking_")
 */
class BookingController extends AbstractController
{
    /**
     * @Route("/search", name="search", methods={"GET", "POST"})
     */
    public function search(
        Request $request,
        CalendarInterface $calendarInterface,
        BookingInterface $bookingInterface
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new Exception('User not authenticated');
        }

        $params = [];

        if ($request->getMethod() === 'POST') {
            $yearStr =  $request->get('year');
            if (!is_string($yearStr)) {
                throw new Exception('Date is not in string format');
            }

            $monthStr =  $request->get('month');
            if (!is_string($monthStr)) {
                throw new Exception('Date is not in string format');
            }

            $todayStr =  $request->get('day');
            if (!is_string($todayStr)) {
                throw new Exception('Date is not in string format');
            }

            $year = (int) $yearStr;
            $month = (int) $monthStr;
            $today = (int) $todayStr;
        } else {
            $year = (int) date("Y");
            $month = (int) date("m");
            $today = (int) date("d");
        }

        $params['year'] = $year;
        $params['month'] = $month;
        $params['today'] = $today;

        $calendar = $calendarInterface->makeCalendar($month, $year);
        $params['calendar'] = $calendar;

        $monthFr = $calendarInterface->getFrenchMonth($month);
        $params['monthTrad'] = $monthFr;

        $courts = $bookingInterface->getBookingsPerCourtAndDate($today, $month, $year, false);
        $params['courts'] = $courts;


        return $this->render('booking/search.html.twig', $params);
    }

    /**
     * @Route("/new", name="new", methods={"POST"})
     */
    public function create(
        Request $request,
        CourtRepository $courtRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new Exception('User not authenticated');
        }

        $yearStr =  $request->get('year');
        if (!is_string($yearStr)) {
            throw new Exception('Date is not in string format');
        }

        $monthStr =  $request->get('month');
        if (!is_string($monthStr)) {
            throw new Exception('Date is not in string format');
        }

        $dayStr =  $request->get('day');
        if (!is_string($dayStr)) {
            throw new Exception('Date is not in string format');
        }

        $courtStr =  $request->get('court');
        if (!is_string($dayStr)) {
            throw new Exception('Court is not in string format');
        }

        $hourStr =  $request->get('hour');
        if (!is_string($dayStr)) {
            throw new Exception('Hour is not in string format');
        }

        $month = sprintf("%02d", $monthStr);
        $day = sprintf("%02d", $dayStr);
        $court = (int) $courtStr;
        $hour = (int) $hourStr;

        $court = $courtRepository->findOneBy(['id' => $court]);
        if (null === $court) {
            throw new Exception('Court is not defined');
        }

        $courtName = $court->getName();
        $dateTime = new DateTime("$yearStr-$month-$day");

        $booking = new Booking();

        $booking->setUser($user);
        $booking->setCourt($court);
        $booking->setHour($hour);
        $booking->setDate($dateTime);

        $entityManager->persist($booking);

        $entityManager->flush();

        $this->addFlash('green', "Votre réservation du $day/$month/$yearStr à $hour"
            . "h00 pour le $courtName est confirmée");

        return $this->redirectToRoute('profile');
    }
}
