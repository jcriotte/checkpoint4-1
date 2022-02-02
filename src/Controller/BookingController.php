<?php

namespace App\Controller;

use App\Service\CalendarInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/booking", name="booking_")
 */
class BookingController extends AbstractController
{
    /**
     * @Route("/search", name="search", methods={"GET", "POST"})
     */
    public function index(CalendarInterface $calendarInterface): Response
    {
        $params = [];

        $year = (int) date("Y");
        $month = (int) date("m");
        $today = (int) date("d");
        $params['year'] = $year;
        $params['month'] = $month;
        $params['today'] = $today;


        $calendar = $calendarInterface->makeCalendar($month, $year);
        $params['calendar'] = $calendar;

        $monthFr = $calendarInterface->getFrenchMonth($month);
        $params['monthTrad'] = $monthFr;


        return $this->render('booking/search.html.twig', $params);
    }
}
