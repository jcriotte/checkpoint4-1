<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Booking;
use App\Entity\Court;
use App\Service\BookingInterface;
use Symfony\Component\Mime\Email;
use App\Service\CalendarInterface;
use App\Repository\CourtRepository;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
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
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new Exception('User not authenticated');
        }

        $yearStr =  $request->get('year');

        $monthStr =  $request->get('month');

        $dayStr =  $request->get('day');

        $courtStr =  $request->get('court');

        $hourStr =  $request->get('hour');

        $params = [$yearStr, $monthStr, $dayStr, $courtStr, $hourStr];

        foreach ($params as $param) {
            if (!is_string($param)) {
                throw new Exception('Param is not in string format');
            }
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

        $pseudo = $user->getPseudo();
        $emailUser = $user->getEmail();
        if (!is_string($emailUser) || !is_string($this->getParameter('mailer_from'))) {
            throw new Exception('Email is not of type string');
        }

        $email = (new Email())
            ->from($this->getParameter('mailer_from'))
            ->to($emailUser)
            ->subject("Nouvelle réservation - Tout court")
            ->html($this->renderView('mail/newBooking.html.twig', [
                'pseudo' => $pseudo,
                'name' => $courtName,
                'date' => "$day/$month/$yearStr",
                'hour' => $hour
            ]));

        $mailer->send($email);


        $this->addFlash('green', "Votre réservation du $day/$month/$yearStr à $hour"
            . "h00 pour le $courtName est confirmée");

        return $this->redirectToRoute('profile');
    }

    /**
     * @Route("/{id}", name="delete", methods={"POST"})
     */
    public function delete(
        Request $request,
        Booking $booking,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (!$user instanceof User || !$booking->getUser() instanceof User) {
            throw new Exception('User not authenticated');
        }

        if ($booking->getUser()->getId() !== $user->getId()) {
            throw new Exception('Delete request denied');
        }

        if (!is_string($request->request->get('_token'))) {
            throw new Exception('Token not available');
        }

        if ($this->isCsrfTokenValid('delete' . $booking->getId(), $request->request->get('_token'))) {
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        if (!$booking->getCourt() instanceof Court) {
            throw new Exception('Court not retrieved');
        }

        $courtName = $booking->getCourt()->getName();

        $pseudo = $user->getPseudo();
        $emailUser = $user->getEmail();
        if (!is_string($emailUser) || !is_string($this->getParameter('mailer_from'))) {
            throw new Exception('Email is not of type string');
        }

        $email = (new Email())
            ->from($this->getParameter('mailer_from'))
            ->to($emailUser)
            ->subject("Annulation de réservation - Tout court")
            ->html($this->renderView('mail/deleteBooking.html.twig', [
                'pseudo' => $pseudo,
                'name' => $courtName
            ]));

        $mailer->send($email);

        $this->addFlash('yellow', "Votre réservation du $courtName est annulée");

        return $this->redirectToRoute('profile');
    }
}
