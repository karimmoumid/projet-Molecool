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
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class MessageController extends AbstractController
{

    #[Route('/messages/inbox', name: 'messages_inbox')]
    public function inbox(Request $request,
                          MessageRepository $messageRepository,
                          PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();

        // Vérifie que l'utilisateur est connecté et du bon type
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos messages.');
        }

        $query = $messageRepository->findInboxMessages($user);
        $messages = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Limite par page
        );

        return $this->render('message/inbox.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/messages/envoye', name: 'messages_sent')]
    public function sent(Request $request,
                         MessageRepository $messageRepository,
                         PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        $query = $messageRepository->findSentMessages($user);
        $messages = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Limite par page
        );

        return $this->render('message/sent.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/messages/favoris', name: 'messages_favorite')]
    public function favorite(Request $request,
                             MessageRepository $messageRepository,
                             PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        $query = $messageRepository->getFavoriteMessagesQuery($user);

        $messages = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Limite par page
        );

        return $this->render('message/favorites.html.twig', [
            'messages' => $messages
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
            $message->setContent('<div class="send"> <p>'. htmlspecialchars($content) . '</p> <time datetime="'. $isoDate . '"> Envoyer le : ' . $formattedDate .'</time></div>');
            $message->setModifyAt($now);
            $message->setIsRead(false);
            $message->setIsSenderDelete(false);
            $message->setIsRecipientDelete(false);
            $em->persist($message);
            $em->flush();


            // Envoi de l'email automatique
            $emailSender->sender('no-reply@tonsite.com',$message->getRecipient()->getEmail(), 'Nouveau message reçu sur votre compte', 'new_message',[
                'recipient' => $message->getRecipient(),
                'sender' => $user,
                'message' => $message
            ]);

            $this->addFlash('success', 'Message envoyé avec succès.');

            return $this->redirectToRoute('messages_inbox');
        }

        return $this->render('message/send.html.twig', [
            'messageForm' => $form,
        ]);
    }


    #[Route('/messages/view/{id}/{read}', name: 'message_view', requirements: ['read' => '0|1'])]
    public function view(EmailService $emailService,bool $read,Message $message, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        // Vérifier que le user est soit sender soit recipient
        $this->denyAccessUnlessGranted('MESSAGE_VIEW', $message);

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
            $message->setIsSenderDelete(false);
            $message->setIsRecipientDelete(false);
if($sender === $user){
$message->setContent('<div class="send"> <p>'. htmlspecialchars($form->get('reponse')->getData()) . '</p> <time datetime="'. $isoDate . '"> Envoyer le : ' . $formattedDate .'</time></div>'.$previewContent);
}
if ($recipient === $user) {
    $message->setContent('<div class="receive"> <p>'. htmlspecialchars($form->get('reponse')->getData()) . '</p> <time datetime="'. $isoDate . '"> Envoyer le : ' . $formattedDate .'</time></div>'.$previewContent);
    $message->setIsResponse(true);
}
$em->flush();
            switch ($message->getLastSender()) {
                case $sender->getName():
                    $emailService->sender('no-reply@tonsite.com',$recipient->getEmail(), 'Nouveau message reçu sur votre compte', 'new_message',[
                        'recipient' => $message->getRecipient(),
                        'sender' => $user,
                        'message' => $message,
                    ]);
                    break;

                case $recipient->getName():
                    $emailService->sender('no-reply@tonsite.com',$sender->getEmail(), 'Nouveau message reçu sur votre compte', 'new_message',[
                        'recipient' => $message->getRecipient(),
                        'sender' => $user,
                        'message' => $message
                    ]);
                    break;

                default:
                    // Code si aucune des conditions ci-dessus n'est remplie
                    break;
            }


return $this->redirectToRoute('message_view', ['id' => $message->getId(), 'read' => 0]);
        }
        if($read){
            $message->setIsRead(true);
            $em->flush();
        }
        return $this->render('message/view.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }



    #[Route('/messages/toggle-favorite/{id}', name: 'message_toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(Message $message, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Utilisateur non connecté'], 401);
        }

        // On vérifie si le message est déjà dans les favoris de l'utilisateur
        if ($user->getFavorite()->contains($message)) {
            $user->removeFavorite($message);
            $favori = false;
        } else {
            $user->addFavorite($message);
            $favori = true;
        }

        $em->flush();

        return new JsonResponse([
            'success' => true,
            'isFavorite' => $favori,
        ]);
    }

    #[Route('/messages/{id}/delete', name: 'message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $csrfHeader = $request->headers->get('X-CSRF-TOKEN');
        $this->denyAccessUnlessGranted('MESSAGE_DELETE', $message);

        if (!$this->isCsrfTokenValid('delete' . $message->getId(), $csrfHeader)) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
        }

        $isSender = $message->getSender() === $user;
        $isRecipient = $message->getRecipient() === $user;

        if (!$isSender && !$isRecipient) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        if ($isSender) {
            $message->setIsSenderDelete(true);
        }

        if ($isRecipient) {
            $message->setIsRecipientDelete(true);
        }

        if ($message->isSenderDelete() && $message->isRecipientDelete()) {
            $em->remove($message);
        }

        $em->flush();

        return new JsonResponse(['success' => true]);
    }




}
