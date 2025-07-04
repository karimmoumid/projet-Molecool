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
    // Route pour la page d'accueil
    #[Route('/', name: 'app_main')]
    public function index(Request $request, EmailService $emailService): Response
    {
        // Création du formulaire de contact
        $form = $this->createForm(ContactUsForm::class);
        $form->handleRequest($request);

        // Traitement du formulaire soumis
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $message = $form->get('message')->getData();
            $subject = $form->get('subject')->getData();

            // Envoi de l'email
            $emailService->sender(
                $email,
                'karimmoumid@gmail.com',
                $subject,
                'contact_us',
                compact('message')
            );

            // Message flash pour confirmer l'envoi
            $this->addFlash('success', 'Votre message a bien été envoyé.');

            // Redirection vers la page d'accueil avec un fragment pour la section contact
            return $this->redirectToRoute('app_main', [], 302, ['fragment' => 'contact']);
        }

        // Affichage de la page d'accueil avec le formulaire
        return $this->render('main/index.html.twig', compact('form'));
    }

    // Route pour la page FAQ
    #[Route('/faq', name: 'app_main_faq')]
    public function faq(): Response
    {
        // Affichage de la page FAQ
        return $this->render('main/faq.html.twig');
    }
}
