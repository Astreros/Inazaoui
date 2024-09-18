<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    #[Route('/guests', name: 'guests')]
    public function guests(): Response
    {
        $guests = $this->entityManager->getRepository(User::class)->findBy([
            'admin' => false,
            'restricted' => false
        ]);

        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id): Response
    {
        $guest = $this->entityManager->getRepository(User::class)->find($id);

        if (!$guest) {
            return $this->redirectToRoute('guests');
        }

        if ($guest->isRestricted()) {
            return $this->redirectToRoute('guests');
        }

        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    #[Route('/portfolio/{id}', name: 'portfolio')]
    public function portfolio(?int $id = null): Response
    {
        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        $album = $id ? $this->entityManager->getRepository(Album::class)->find($id) : null;

        $medias = $album
            ? $this->entityManager->getRepository(Media::class)->findAllMediasNotRestrictedByAlbum($album)
            : $this->entityManager->getRepository(Media::class)->findAllMediasNotRestricted();

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}