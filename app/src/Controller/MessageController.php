<?php

// src/Controller/MessageController.php
namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageForm;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{

    #[Route('/messages/inbox', name: 'messages_inbox')]
    public function inbox(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $messages = $em->getRepository(Message::class)->findBy(['recipient' => $user], ['created_at' => 'DESC']);

        return $this->render('message/inbox.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/messages/send', name: 'message_send')]
    public function send(EmailService $emailSender, Request $request, EntityManagerInterface $em, MailerInterface $mailer, UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        $message = new Message();
        $patients = $userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_PATIENT%')
            ->getQuery()
            ->getResult();
        $employees = $userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_EMPLOYEE%')
            ->getQuery()
            ->getResult();

        $form = $this->createForm(MessageForm::class, $message, [
            'currentUser' => $user,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSender($user);
            $message->setCreatedAt(new \DateTimeImmutable());

            $em->persist($message);
            $em->flush();

            // Envoi de l'email automatique
            $emailSender->sender('no-reply@tonsite.com',$message->getRecipient()->getEmail(), 'Nouveau message reçu sur votre compte', 'new_message',[
                'recipient' => $message->getRecipient(),
                'sender' => $user,
                'message' => $message,
            ]);

            $this->addFlash('success', 'Message envoyé avec succès.');

            return $this->redirectToRoute('messages_inbox');
        }

        return $this->render('message/send.html.twig', [
            'messageForm' => $form->createView(),
            'patients' => $patients,
            'employees' => $employees
        ]);
    }


    #[Route('/messages/view/{id}', name: 'message_view')]
    public function view(Message $message): Response
    {
        $user = $this->getUser();

        // Vérifier que le user est soit sender soit recipient
        if ($message->getSender() !== $user && $message->getRecipient() !== $user) {
            throw $this->createAccessDeniedException('Vous n’êtes pas autorisé à voir ce message.');
        }

        return $this->render('message/view.html.twig', [
            'message' => $message,
        ]);
    }
}
