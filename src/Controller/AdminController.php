<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Entity\Booking;
use App\Service\BookingInterface;
use App\Service\CalendarInterface;
use App\Service\HandleBookingsSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/bookings", name="bookings", methods={"GET", "POST"})
     */
    public function booking(
        Request $request,
        CalendarInterface $calendarInterface,
        BookingInterface $bookingInterface,
        HandleBookingsSearch $searchHandler
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new Exception('User not authenticated');
        }

        $params = [];

        $postData = $searchHandler->setParams($request);

        $year = $postData['year'];
        $month = $postData['month'];
        $today = $postData['today'];

        $params['year'] = $year;
        $params['month'] = $month;
        $params['today'] = $today;

        $calendar = $calendarInterface->makeCalendar($month, $year);
        $params['calendar'] = $calendar;

        $monthFr = $calendarInterface->getFrenchMonth($month);
        $params['monthTrad'] = $monthFr;

        $courts = $bookingInterface->getBookingsPerCourtAndDate($today, $month, $year, true);
        $params['courts'] = $courts;

        return $this->render('admin/bookings.html.twig', $params);
    }

    /**
     * @Route("/booking/{id}", name="booking_delete", methods={"POST"})
     */
    public function delete(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        if (!is_string($token)) {
            throw new Exception('Token not valid');
        }

        if ($this->isCsrfTokenValid('delete' . $booking->getId(), $token)) {
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_bookings', [], Response::HTTP_SEE_OTHER);
    }
}
