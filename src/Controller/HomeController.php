<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Form\ProfileEditFormType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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

        $bookings = $bookingRepository->findBy(['user' => $user]);

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
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
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
            if (!is_string($currentPassword)) {
                throw new Exception('Password is not of type string');
            }

            $isPasswordValid = $this->checkCurrentPassword($currentPassword, $user, $userPasswordHasher);

            if (!$isPasswordValid) {
                $errorConfirmation = 'Wrong password';
                return $this->render('home/profile_edit.html.twig', [
                    'registrationForm' => $form->createView(),
                    'errorConfirmation' => $errorConfirmation
                ]);
            }

            $plainPassword = $form->get('plainPassword')->getData();
            if (!is_string($plainPassword)) {
                throw new Exception('Password is not of type string');
            }
            $this->handleNewPasswodRequest($plainPassword, $user, $userPasswordHasher);

            $entityManager->flush();

            $this->addFlash('green', 'Your profile has been updated');

            return $this->redirectToRoute('profile');
        }

        return $this->render('home/profile_edit.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Profile/{id}", name="profile_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
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

    private function checkCurrentPassword(
        string $currentPassword,
        User $user,
        UserPasswordHasherInterface $userPasswordHasher
    ): bool {

        return $userPasswordHasher->isPasswordValid($user, $currentPassword);
    }

    private function handleNewPasswodRequest(
        ?string $plainPassword,
        User $user,
        UserPasswordHasherInterface $userPasswordHasher
    ): void {
        if (null != $plainPassword) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );
        }
    }
}
