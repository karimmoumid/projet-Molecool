<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordForm;
use App\Form\ResetPasswordRequestForm;
use App\Form\UserForm;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
    #[Route('/services', name: 'app_user')]
    #[isGranted('ROLE_PATIENT')]
    public function index(): Response
    {
        return $this->render('user/show.html.twig');
    }

    #[Route('/services_employee_admin', name: 'app_user_employee_admin')]
    #[isGranted('ROLE_EMPLOYEE')]
    public function services(): Response
    {
        return $this->render('user/services.html.twig');
    }






    #[Route('/reinitialisaiton-de-mot-de-passe/request', name: 'app_forgot_password_request')]
    public function request(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        EmailService $emailService
    ): Response
    {
        $form = $this->createForm(ResetPasswordRequestForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            // Chercher l'utilisateur par email
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                // Générer un token unique
                $resetToken = bin2hex(random_bytes(32));
                $user->setResetToken($resetToken);
                // Définir une date d'expiration, par exemple 1h plus tard
                $user->setResetTokenExpiredAt(new \DateTimeImmutable('+1 hour'));

                $em->flush();

                // Envoyer le mail avec le lien (exemple simple)
                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

                $emailService->sender('no-reply@example.com', $user->getEmail(),'Réinitialisation de votre mot de passe','reset_password',[
                    'resetUrl' => $resetUrl,
                    'username' => $user->getName()
                ]);

            }

            // Toujours afficher un message générique pour ne pas révéler si l'email existe
            $this->addFlash('success', 'Si votre adresse existe dans notre base, vous allez recevoir un email.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/reset_password_request.html.twig', compact('form')
        );
    }

    #[Route('/reset-password/reset/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Rechercher l'utilisateur avec le token
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetTokenExpiredAt() < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Le lien de réinitialisation est invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ResetPasswordForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData(); // Assurez-vous que le champ existe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

            $user->setPassword($hashedPassword);
            $user->setResetToken(null);
            $user->setResetTokenExpiredAt(null);

            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été réinitialisé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/reset_password.html.twig', compact('form'));
    }


}
