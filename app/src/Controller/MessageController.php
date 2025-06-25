<?php

// src/Controller/MessageController.php
namespace App\Controller;

use App\Entity\File;
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
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function sent(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        $messages = $messageRepository->findSentMessages($user);

        return $this->render('message/sent.html.twig', [
            'messages' => $messages,
        ]);
    }



    #[Route('/messages/send', name: 'message_send')]
    public function send(EmailService $emailSender, Request $request, EntityManagerInterface $em, UserRepository $userRepository, SluggerInterface $slugger): Response
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
            $files = $form->get('files')->getData();
            foreach ($files as $file) {
                if ($file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $cleanFilename = strtolower($slugger->slug($originalFilename));
                    $newFilename = $cleanFilename . '-' . uniqid() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('secure_uploads_directory'), $newFilename);

                    $piece = new File();
                    $piece->setname($newFilename);
                    $piece->setOriginalName($cleanFilename);
                    $em->persist($piece);
                    $message->addFile($piece);
                }
            }

            $message->setSender($user);
            $message->setLastSender($user->getName());
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
            $message->setIsRead(false);
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


    #[Route('/messages/view/{id}/{read}', name: 'message_view', requirements: ['read' => '0|1'])]
    public function view(bool $read,Message $message, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        // Vérifier que le user est soit sender soit recipient
        if ($message->getSender() !== $user && $message->getRecipient() !== $user) {
            throw $this->createAccessDeniedException('Vous n’êtes pas autorisé à voir ce message.');
        }
$message->setIsRead(true);
        $form = $this->createForm(AnswerForm::class, $message,[]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $files = $form->get('files')->getData();
            foreach ($files as $file) {
                if ($file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $cleanFilename = strtolower($slugger->slug($originalFilename));
                    $newFilename = $cleanFilename . '-' . uniqid() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('secure_uploads_directory'), $newFilename);

                    $piece = new File();
                    $piece->setname($newFilename);
                    $piece->setOriginalName($cleanFilename);
                    $em->persist($piece);
                    $message->addFile($piece);
                }
            }
            $now = new \DateTimeImmutable();
            $message->setModifyAt($now);
            $sender = $message->getSender();
            $recipient = $message->getRecipient();
            $previewContent = $message->getContent();
            $formattedDate = $now->format('Y-m-d H:i');
            $isoDate = $now->format('c');
            $message->setIsRead(false);
            $message->setLastSender($user->getName());
if($sender === $user){
$message->setContent('<div class="send"> <p>'. $form->get('reponse')->getData() . '</p> <time datetime="'. $isoDate . '"> Envoyer le : ' . $formattedDate .'</time></div>'.$previewContent);
}
if ($recipient === $user) {
    $message->setContent('<div class="receive"> <p>'. $form->get('reponse')->getData() . '</p> <time datetime="'. $isoDate . '"> Envoyer le : ' . $formattedDate .'</time></div>'.$previewContent);
    $message->setIsResponse(true);
}
$em->persist($message);
$em->flush();
return $this->redirectToRoute('message_view', ['id' => $message->getId(), 'read' => 0]);
        }
        if($read){
            $em->persist($message);
            $em->flush();
        }
        return $this->render('message/view.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }
}
