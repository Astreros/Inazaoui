<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\User;
use App\Form\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class MediaController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/admin/media', name: 'admin_media_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $criteria = [];

        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $medias = $this->entityManager->getRepository(Media::class)->findBy(
            $criteria,
            ['id' => 'ASC'],
            25,
            25 * ($page - 1)
        );
        $total = $this->entityManager->getRepository(Media::class)->count([]);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }

    #[Route('/admin/media/add', name: 'admin_media_add')]
    public function add(Request $request): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$this->isGranted('ROLE_ADMIN')) {
                $user = $this->getUser();

                if ($user instanceof User){
                    $media->setUser($user);
                }
            }

            $file = $media->getFile();
            if ($file !== null) {
                $media->setPath('uploads/' . md5(uniqid('', true)) . '.' . $file->guessExtension());
                $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/', $media->getPath());
            } else {
                $this->addFlash('error', "Aucun fichier n'a été téléchargé.");
                return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
            }

            $this->entityManager->persist($media);
            $this->entityManager->flush();

            $this->addFlash('success', 'Média ajouté avec succès.');

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/admin/media/delete/{id}', name: 'admin_media_delete')]
    public function delete(int $id): RedirectResponse
    {
        $media = $this->entityManager->getRepository(Media::class)->find($id);

        if ($media === null) {
            $this->addFlash('error', 'Media not found.');
            return $this->redirectToRoute('admin_media_index');
        }

        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN') || ($media->getUser() === $user)) {
            $this->entityManager->remove($media);
            $this->entityManager->flush();

            $path = $media->getPath();
            if ($path !== null) {
                $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . $path;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

        } else {
            return $this->redirectToRoute('admin_media_index');
        }

        return $this->redirectToRoute('admin_media_index');
    }
}