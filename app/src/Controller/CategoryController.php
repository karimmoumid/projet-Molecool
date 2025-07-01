<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryForm;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CategoryController extends AbstractController
{
    // Route pour afficher toutes les catégories (accessible sans restriction)
    #[Route('/categories', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        // Récupération de toutes les catégories depuis le repository
        $categories = $categoryRepository->findAll();
        // Affichage de la vue avec la liste des catégories
        return $this->render('category/index.html.twig', compact('categories'));
    }

    // Route sécurisée pour les admins affichant la liste des catégories (GET uniquement)
    #[Route('/liste/categories',name: 'app_categories_list', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function show(CategoryRepository $categoryRepository): Response
    {
        // Rend la vue avec toutes les catégories
        return $this->render('category/show.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    // Route pour ajouter une nouvelle catégorie, accessible aux admins (GET et POST)
    #[Route('/ajouter/categorie', name: 'app_categories_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une nouvelle entité Category vide
        $category = new Category();
        // Création du formulaire associé à l'entité
        $form = $this->createForm(CategoryForm::class, $category);
        // Traitement de la requête HTTP avec le formulaire
        $form->handleRequest($request);

        // Vérification que le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les équipements depuis le formulaire (probablement un tableau)
            $equipements = $form->get('equipements')->getData();
            // Initialisation d'une liste HTML ul
            $htmlList = '<ul>';

            // Boucle pour chaque équipement pour construire la liste HTML
            foreach ($equipements as $equipement) {
                $htmlList .= '<li>' . $equipement . '</li>';
            }

            $htmlList .= '</ul>';

            // Assigne la liste HTML à l'attribut equipment de la catégorie
            $category->setEquipment($htmlList);

            // Persiste la catégorie en base
            $entityManager->persist($category);
            $entityManager->flush();

            // Redirige vers la liste des catégories après création
            return $this->redirectToRoute('app_categories_list', [], Response::HTTP_SEE_OTHER);
        }

        // Affiche le formulaire pour créer une nouvelle catégorie
        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    // Route pour modifier une catégorie existante, sécurisée pour les admins (GET et POST)
    #[Route('/categorie/{id}/modifier', name: 'app_categories_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire pré-rempli avec la catégorie existante
        $form = $this->createForm(CategoryForm::class, $category);
        // Traitement de la requête HTTP avec le formulaire
        $form->handleRequest($request);

        // Vérifie que le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les équipements modifiés depuis le formulaire
            $equipements = $form->get('equipements')->getData();
            // Reconstruction de la liste HTML ul
            $htmlList = '<ul>';

            foreach ($equipements as $equipement) {
                $htmlList .= '<li>' . $equipement . '</li>';
            }
            $htmlList .= '</ul>';

            // Met à jour l'attribut equipment de la catégorie
            $category->setEquipment($htmlList);

            // Sauvegarde les modifications en base
            $entityManager->flush();

            // Redirige vers la liste des catégories après modification
            return $this->redirectToRoute('app_categories_list', [], Response::HTTP_SEE_OTHER);
        }

        // Affiche le formulaire d'édition de la catégorie
        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    // Route pour supprimer une catégorie, sécurisée pour les admins, méthode POST uniquement
    #[Route('/categorie/supprimer/{id}', name: 'app_categories_delete', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        // Vérifie la validité du token CSRF pour la suppression
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            // Supprime la catégorie de la base de données
            $entityManager->remove($category);
            $entityManager->flush();
        }

        // Redirige vers la liste des catégories après suppression
        return $this->redirectToRoute('app_categories_list', [], Response::HTTP_SEE_OTHER);
    }



}
