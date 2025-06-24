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
    #[Route('/categories', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('category/index.html.twig', compact('categories'));
    }

    #[Route('/liste/categories',name: 'app_categories_list', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function show(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/show.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/ajouter/categorie', name: 'app_categories_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryForm::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $equipements = $form->get('equipements')->getData();
            $htmlList = '<ul>';

            foreach ($equipements as $equipement) {
                $htmlList .= '<li>' . $equipement . '</li>';
            }

            $htmlList .= '</ul>';

            $category->setEquipment($htmlList);

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_categories_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/categorie/{id}/modifier', name: 'app_categories_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryForm::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $equipements = $form->get('equipements')->getData();
            $htmlList = '<ul>';

            foreach ($equipements as $equipement) {
                $htmlList .= '<li>' . $equipement . '</li>';
            }
            $htmlList .= '</ul>';

            $category->setEquipment($htmlList);

            $entityManager->flush();

            return $this->redirectToRoute('app_categories_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/categorie/supprimer/{id}', name: 'app_categories_delete', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categories_list', [], Response::HTTP_SEE_OTHER);
    }



}
