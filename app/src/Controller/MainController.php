<?php

namespace App\Controller;

use App\Form\ContactUsForm;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(Request $request, EmailService $emailService): Response
    {
        $form = $this->createForm(ContactUsForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $message = $form->get('message')->getData();
            $subject = $form->get('subject')->getData();
            $emailService->sender($email,'laboratoire@analyse.com',$subject,'contact_us',compact('message'));

            $this->addFlash('success', 'Votre message a bien été envoyé.');

            return $this->redirectToRoute('app_main', [], 302, ['fragment' => 'contact']);

        }

        return $this->render('main/index.html.twig',compact('form'));
    }

    #[Route('/faq', name: 'app_main_faq')]
    public function faq(): Response
    {
        return $this->render('main/faq.html.twig');
    }



}
