<?php

namespace App\Tests\Controller\Admin;

use App\DataFixtures\AppFixtures;
use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get(null);

        $databaseTool->loadFixtures([
            AppFixtures::class
        ]);
    }

    public function testAdminCanAccessAllMedias(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/media');
        self::assertResponseIsSuccessful();

        $medias = $this->entityManager->getRepository(Media::class)->findAll();
        foreach ($medias as $media) {
            $title = $media->getTitle();
            self::assertNotNull($title);

            self::assertAnySelectorTextContains('td', $title);
        }
    }

    public function testUserCanAccessOwnMediasOnly(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => false,
        ]);
        self::assertNotNull($user);

        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($user);

        $this->client->request('GET', '/admin/media');
        self::assertResponseIsSuccessful();

        $ownedMedias = $this->entityManager->getRepository(Media::class)->findBy([
            'user' => $user,
        ]);
        foreach ($ownedMedias as $media) {
            $title = $media->getTitle();
            self::assertNotNull($title);

            self::assertAnySelectorTextContains('td', $title);
        }

        $adminMedias = $this->entityManager->getRepository(Media::class)->findBy([
            'user' => $admin,
        ]);
        foreach ($adminMedias as $media) {
            $title = $media->getTitle();
            self::assertNotNull($title);

            self::assertAnySelectorTextNotContains('td', $title);
        }
    }

    public function testAdminCanAddMedia(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $album = $this->entityManager->getRepository(Album::class)->findOneBy([]);
        self::assertNotNull($album);

        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/admin/media/add');
        self::assertResponseIsSuccessful();

        $imagePath = self::getContainer()->getParameter('kernel.project_dir') . '/imagesTest/test8183107.jpg';
        $file = new UploadedFile($imagePath, 'test8183107', 'image/jpeg', null, true);

        $form = $crawler->selectButton('Ajouter')->form([
            'media[user]' => $admin->getId(),
            'media[album]' => $album->getId(),
            'media[title]' => 'Titre image test',
            'media[file]' => $file
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/media');

        $media = $this->entityManager->getRepository(Media::class)->findOneBy([
            'title' => 'Titre image test',
        ]);
        self::assertNotNull($media);
        self::assertSame('Titre image test', $media->getTitle());
        self::assertNotNull($media->getPath());
        self::assertFileExists(self::getContainer()->getParameter('kernel.project_dir') . '/public/' . $media->getPath());

        unlink(self::getContainer()->getParameter('kernel.project_dir') . '/public/' . $media->getPath()); // Supprime le fichier
    }

//    private function createFile(string $filename): UploadedFile
//    {
//        $path = self::getContainer()->getParameter('kernel.project_dir') . '/public/uploads/' . $filename;
//
//        file_put_contents($path, 'Contenu de test');
//
//        return new UploadedFile(
//            $path,
//            $filename,
//            'image/jpeg',
//            null,
//            true
//        );
//    }

    protected function tearDown(): void
    {
        $this->entityManager->close();

        parent::tearDown();
    }
}