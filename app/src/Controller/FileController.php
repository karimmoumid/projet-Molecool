<?php
// src/Controller/FileController.php

namespace App\Controller;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FileController extends AbstractController
{
    #[Route('/secure-file/{id}', name: 'secure_file')]
    public function serveFile(int $id, EntityManagerInterface $em)
    {
        $file = $em->getRepository(File::class)->find($id);
        if (!$file) {
            throw $this->createNotFoundException('Fichier non trouvé');
        }

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à ce fichier.');
        }

        $message = $file->getMessage();

        // Vérifie si l’utilisateur a accès au message (à adapter selon ta logique)
        if (!$message->isUserAllowed($user)) {
            throw new AccessDeniedException('Vous n’avez pas la permission d’accéder à ce fichier.');
        }

        $uploadsDir = $this->getParameter('secure_uploads_directory');
        $filePath = $uploadsDir . '/' . $file->getName();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Fichier introuvable sur le serveur.');
        }

        $response = new BinaryFileResponse($filePath);
        $downloadName = $file->getOriginalName() ?: $file->getName();

// Assure qu'il contient une extension (fallback si nécessaire)
        if (!pathinfo($downloadName, PATHINFO_EXTENSION)) {
            $extension = pathinfo($file->getName(), PATHINFO_EXTENSION);
            $downloadName .= '.' . $extension;
        }

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $downloadName
        );


        return $response;
    }
}
