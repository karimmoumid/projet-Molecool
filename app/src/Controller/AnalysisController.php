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
    #[Route('/analyses/{id}', name: 'app_analysis')]
    public function index(int $id, CategoryRepository $categoryRepository): Response
    {
$category = $categoryRepository->find($id);
if (!$category) {
    throw $this->createNotFoundException("Catégorie non trouvée pour l'ID $id");
}
$analyses = $category->getAnalysis();
        return $this->render('analysis/index.html.twig', compact('analyses', 'category'));
    }

    #[Route('/analyses', name: 'app_analysis_list')]
    #[IsGranted("ROLE_ADMIN")]
    public function show(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('analysis/show.html.twig', compact('categories'));
    }


    #[Route('/analyse/ajouter', name: 'app_analysis_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $analysi = new Analysis();
        $form = $this->createForm(AnalysisForm::class, $analysi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $description = $form->get('description')->getData();
            $price = $form->get('prix')->getData();
            $analysi->setDescription('<article>
            <div>
                <h3>' . $analysi->getName() . '</h3>
                <p>' . $price . '€</p>
            </div>
            <p>' . $description .'</p>
        </article>');
            $entityManager->persist($analysi);
            $entityManager->flush();

            return $this->redirectToRoute('app_analysis_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('analysis/new.html.twig', [
            'analysi' => $analysi,
            'form' => $form,
        ]);
    }

    #[Route('/analyse/{id}/modifier', name: 'app_analysis_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function edit(Request $request, Analysis $analysi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnalysisForm::class, $analysi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $description = $form->get('description')->getData();
            $price = $form->get('prix')->getData();
            $analysi->setDescription('<article>
            <div>
                <h3>' . $analysi->getName() . '</h3>
                <p>' . $price . '€</p>
            </div>
            <p>' . $description .'</p>
        </article>');
            $entityManager->flush();

            return $this->redirectToRoute('app_analysis_list',[], Response::HTTP_SEE_OTHER);
        }

        return $this->render('analysis/edit.html.twig', [
            'analysi' => $analysi,
            'form' => $form,
        ]);
    }

    #[Route('/analyse/{id}', name: 'app_analysis_delete', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(Request $request, Analysis $analysi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$analysi->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($analysi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_analysis_list', [], Response::HTTP_SEE_OTHER);
    }

}

