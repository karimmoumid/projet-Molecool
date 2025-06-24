<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/inscription/{role}', name: 'app_register')]
    public function register(string $role, Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $allowedRoles = ['admin', 'employee', 'customer'];
        if (!in_array($role, $allowedRoles, true)) {
            throw $this->createNotFoundException('Rôle non reconnu');
        }

        if (in_array($role, ['admin', 'employee'], true)) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
            $user = new User();
            $form = $this->createForm(RegistrationForm::class, $user,['pro' => true]);
        }else{
            $user = new User();
            $form = $this->createForm(RegistrationForm::class, $user);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            switch ($role) {
                case 'admin':
                    $user->setRoles(['ROLE_ADMIN']);
                    break;
                case 'employee':
                    $user->setRoles(['ROLE_EMPLOYEE']);
                    break;
                case 'customer':
                default:
                    $user->setRoles(['ROLE_CUSTOMER']);
                    break;
            }

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            if($role === 'customer') {
                return $security->login($user, 'form_login', 'main');
            }
            return $this->redirectToRoute('app_user_pro_list');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'role' => $role
        ]);
    }
}
