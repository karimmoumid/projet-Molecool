<?php

// src/Controller/MessageController.php
namespace App\Controller;

use App\Entity\Message;
use App\Form\AnswerForm;
use App\Form\MessageForm;
use App\Repository\MessageRepository;
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
    public function inbox(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();

        // Vérifie que l'utilisateur est connecté et du bon type
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos messages.');
        }

        $messages = $messageRepository->findInboxMessages($user);

        return $this->render('message/inbox.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/messages/envoye', name: 'messages_sent')]
    public function sent(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $messages = $em->getRepository(Message::class)->findBy(['sender' => $user], ['modify_at' => 'DESC']);

        return $this->render('message/sent.html.twig', [
            'messages' => $messages,
        ]);
    }



    #[Route('/messages/send', name: 'message_send')]
    public function send(EmailService $emailSender, Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $message = new Message();
        if (in_array('ROLE_PATIENT', $user->getRoles())) {
            $form = $this->createForm(MessageForm::class, $message);
        }else{
            $form = $this->createForm(MessageForm::class, $message,[
                'pro'=>true
            ]);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSender($user);
            if ($form->has('patient') && $form->get('patient')->getData()){
                $message->setRecipient($form->get('patient')->getData());
            }else{
                $message->setRecipient($form->get('employer')->getData());
            }
            $now= new \DateTimeImmutable();
            $formattedDate = $now->format('Y-m-d H:i');
            $isoDate = $now->format('c');
            $message->setCreatedAt($now);
            $content = $form->get('content')->getData();
            $message->setContent('<div class="send"> <p>'. $content . '</p> <time datetime="'. $isoDate . '"> Envoyer le : ' . $formattedDate .'</time></div>');
            $message->setModifyAt($now);
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
            'messageForm' => $form,
        ]);
    }


    #[Route('/messages/view/{id}', name: 'message_view')]
    public function view(Message $message, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        // Vérifier que le user est soit sender soit recipient
        if ($message->getSender() !== $user && $message->getRecipient() !== $user) {
            throw $this->createAccessDeniedException('Vous n’êtes pas autorisé à voir ce message.');
        }

        $form = $this->createForm(AnswerForm::class, $message,[]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable();
            $message->setModifyAt($now);
            $sender = $message->getSender();
            $recipient = $message->getRecipient();
            $previewContent = $message->getContent();
            $formattedDate = $now->format('Y-m-d H:i');
            $isoDate = $now->format('c');
if($sender === $user){
$message->setContent('<div class="send"> <p>'. $form->get('reponse')->getData() . '</p> <time datetime="'. $isoDate . '"> Envoyer le : ' . $formattedDate .'</time></div>'.$previewContent);
}
if ($recipient === $user) {
    $message->setContent('<div class="receive"> <p>'. $form->get('reponse')->getData() . '</p> <time datetime="'. $isoDate . '"> Envoyer le : ' . $formattedDate .'</time></div>'.$previewContent);
    $message->setIsResponse(true);
}
$em->persist($message);
$em->flush();
return $this->redirectToRoute('message_view', ['id' => $message->getId()]);
        }

        return $this->render('message/view.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }
}
