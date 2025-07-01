<?php

namespace App\Controller;

use App\Entity\Analysis;
use App\Form\AnalysisForm;
use App\Repository\AnalysisRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AnalysisController extends AbstractController
{
    // Route pour afficher les analyses d'une catégorie spécifique via son ID
    #[Route('/analyses/{id}', name: 'app_analysis')]
    public function index(int $id, CategoryRepository $categoryRepository): Response
    {
        // Récupère la catégorie via le repository
        $category = $categoryRepository->find($id);
        // Si la catégorie n'existe pas, lance une exception 404
        if (!$category) {
            throw $this->createNotFoundException("Catégorie non trouvée pour l'ID $id");
        }
        // Récupère les analyses associées à la catégorie
        $analyses = $category->getAnalysis();
        // Rend la vue avec les analyses et la catégorie
        return $this->render('analysis/index.html.twig', compact('analyses', 'category'));
    }

    // Route sécurisée accessible uniquement aux admins pour afficher toutes les catégories
    #[Route('/analyses', name: 'app_analysis_list')]
    #[IsGranted("ROLE_ADMIN")]
    public function show(CategoryRepository $categoryRepository): Response
    {
        // Récupère toutes les catégories
        $categories = $categoryRepository->findAll();
        // Affiche la vue liste des catégories
        return $this->render('analysis/show.html.twig', compact('categories'));
    }

    // Route pour ajouter une nouvelle analyse, sécurisée pour les admins, accepte GET et POST
    #[Route('/analyse/ajouter', name: 'app_analysis_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une nouvelle entité Analysis vide
        $analysi = new Analysis();
        // Création du formulaire lié à l'entité
        $form = $this->createForm(AnalysisForm::class, $analysi);
        // Traitement de la requête HTTP avec le formulaire
        $form->handleRequest($request);

        // Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère la description et le prix depuis le formulaire
            $description = $form->get('description')->getData();
            $price = $form->get('prix')->getData();

            // Compose le champ description au format HTML incluant nom et prix
            $analysi->setDescription('<article>
            <div>
                <h3>' . $analysi->getName() . '</h3>
                <p>' . $price . '€</p>
            </div>
            <p>' . $description .'</p>
        </article>');

            // Persiste l'entité en base de données
            $entityManager->persist($analysi);
            $entityManager->flush();

            // Redirige vers la liste des analyses après succès
            return $this->redirectToRoute('app_analysis_list', [], Response::HTTP_SEE_OTHER);
        }

        // Affiche le formulaire pour créer une nouvelle analyse
        return $this->render('analysis/new.html.twig', [
            'analysi' => $analysi,
            'form' => $form,
        ]);
    }

    // Route pour modifier une analyse existante, sécurisée pour les admins, accepte GET et POST
    #[Route('/analyse/{id}/modifier', name: 'app_analysis_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function edit(Request $request, Analysis $analysi, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire pré-rempli avec l'analyse existante
        $form = $this->createForm(AnalysisForm::class, $analysi);
        // Traitement de la requête HTTP avec le formulaire
        $form->handleRequest($request);

        // Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère la description et le prix modifiés
            $description = $form->get('description')->getData();
            $price = $form->get('prix')->getData();

            // Met à jour la description formatée HTML
            $analysi->setDescription('<article>
            <div>
                <h3>' . $analysi->getName() . '</h3>
                <p>' . $price . '€</p>
            </div>
            <p>' . $description .'</p>
        </article>');

            // Sauvegarde les modifications en base
            $entityManager->flush();

            // Redirige vers la liste des analyses après succès
            return $this->redirectToRoute('app_analysis_list',[], Response::HTTP_SEE_OTHER);
        }

        // Affiche le formulaire d'édition de l'analyse
        return $this->render('analysis/edit.html.twig', [
            'analysi' => $analysi,
            'form' => $form,
        ]);
    }

    // Route pour supprimer une analyse, sécurisée pour les admins, méthode POST uniquement
    #[Route('/analyse/{id}', name: 'app_analysis_delete', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(Request $request, Analysis $analysi, EntityManagerInterface $entityManager): Response
    {
        // Vérifie la validité du token CSRF pour la suppression
        if ($this->isCsrfTokenValid('delete'.$analysi->getId(), $request->getPayload()->getString('_token'))) {
            // Supprime l'entité Analysis de la base
            $entityManager->remove($analysi);
            $entityManager->flush();
        }

        // Redirige vers la liste des analyses après suppression
        return $this->redirectToRoute('app_analysis_list', [], Response::HTTP_SEE_OTHER);
    }

}
