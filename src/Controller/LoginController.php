<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use Symfony\Component\Mime\Email;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $user = $this->getUser();

        if (null !== $user) {
            return $this->redirectToRoute('profile');
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET", "POST"})
     */
    public function logout(): Response
    {
        // controller can be blank: it will never be called!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }

    /**
     * @Route("/register", name="app_register", methods={"GET", "POST"})
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $formLoginAuth,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $currentUser = $this->getUser();

        if (null !== $currentUser) {
            return $this->redirectToRoute('profile');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $plainPassword = $form->get('plainPassword')->getData();
            if (!is_string($plainPassword)) {
                throw new Exception('Password is not of type string');
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $userAuthenticator->authenticateUser(
                $user,
                $formLoginAuth,
                $request
            );

            $pseudo = $user->getPseudo();
            $emailUser = $user->getEmail();
            if (!is_string($emailUser) || !is_string($this->getParameter('mailer_from'))) {
                throw new Exception('Email is not of type string');
            }

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to($emailUser)
                ->subject("Registration - Aest")
                ->html($this->renderView('registrationEmail.html.twig', [
                    'pseudo' => $pseudo,
                    'email' => $emailUser
                ]));

            $mailer->send($email);

            return $this->redirectToRoute('home');
        }

        return $this->render('login/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
