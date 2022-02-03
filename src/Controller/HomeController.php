<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Form\ProfileEditFormType;
use Symfony\Component\Mime\Email;
use App\Repository\BookingRepository;
use App\Service\UserPasswordInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/profile", name="profile", methods={"GET"})
     */
    public function profile(BookingRepository $bookingRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new Exception('User not authenticated');
        }

        $id = $user->getId();

        if (null === $id) {
            throw new Exception('User not authenticated');
        }

        $bookings = $bookingRepository->findByUserWithoutBookings($id);

        return $this->render('home/profile.html.twig', [
            'user' => $user,
            'bookings' => $bookings,
        ]);
    }

    /**
     * @Route("/profile/edit", name="profile_edit", methods={"GET", "POST"})
     */
    public function profileEdit(
        Request $request,
        UserPasswordInterface $userPassInterface,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new Exception('User not authenticated');
        }

        $form = $this->createForm(ProfileEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $currentPassword = $form->get('confirmPassword')->getData();

            $isPasswordValid = $userPassInterface->checkCurrentPassword($currentPassword, $user);

            if (!$isPasswordValid) {
                $errorConfirmation = 'Wrong password';
                return $this->render('home/profile_edit.html.twig', [
                    'registrationForm' => $form->createView(),
                    'errorConfirmation' => $errorConfirmation
                ]);
            }

            $plainPassword = $form->get('plainPassword')->getData();

            $userPassInterface->handleNewPasswodRequest($plainPassword, $user);

            $entityManager->flush();

            $pseudo = $user->getPseudo();
            $emailUser = $user->getEmail();
            if (!is_string($emailUser) || !is_string($this->getParameter('mailer_from'))) {
                throw new Exception('Email is not of type string');
            }

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to($emailUser)
                ->subject("Mise à jour de profil - Tout court")
                ->html($this->renderView('mail/updateEmail.html.twig', [
                    'pseudo' => $pseudo
                ]));

            $mailer->send($email);

            $this->addFlash('green', 'Votre profil a été mis à jour');

            return $this->redirectToRoute('profile');
        }

        return $this->render('home/profile_edit.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Profile/{id}", name="profile_delete", methods={"POST"})
     */
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new Exception('User not authenticated');
        }

        if (!is_string($request->request->get('_token'))) {
            throw new Exception('Token not available');
        }

        $bookings = $user->getBookings();

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            foreach ($bookings as $booking) {
                $entityManager->remove($booking);
            }
            $entityManager->remove($user);
            $entityManager->flush();
        }

        $session = new Session();
        $session->invalidate();

        return $this->redirectToRoute('home');
    }
}
