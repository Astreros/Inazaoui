<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function guests(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $criteria = [];
        $criteria['admin'] = false;
        $criteria['restricted'] = false;

        $guests = $this->entityManager->getRepository(User::class)->findBy(
            $criteria,
            ['id' => 'ASC'],
            25,
            25 * ($page - 1)
        );

        $total = $this->entityManager->getRepository(User::class)->count($criteria);

        return $this->render('front/guests.html.twig', [
            'guests' => $guests,
            'total' => $total,
            'page' => $page,
        ]);
    }

    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id): Response
    {
        $guest = $this->entityManager->getRepository(User::class)->find($id);

        if ($guest === null) {
            return $this->redirectToRoute('guests');
        }

        if ($guest->isRestricted() === true) {
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
        $album = null;

        if ($id !== null) {
            $album = $this->entityManager->getRepository(Album::class)->find($id);
        }

        if ($album !== null) {
            $medias = $this->entityManager->getRepository(Media::class)->findAllMediasNotRestrictedByAlbum($album);
        } else {
            $medias = $this->entityManager->getRepository(Media::class)->findAllMediasNotRestricted();
        }

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