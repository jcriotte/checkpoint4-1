<?php

namespace App\Controller;

use App\Repository\BookingRepository;
use App\Service\BookingInterface;
use App\Service\CalendarInterface;
use Exception;
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
}
