<?php

namespace App\Tests\Controller\Admin;

use App\DataFixtures\AppFixtures;
use App\Entity\Album;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumControllerTest extends WebTestCase
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

    public function testIndexAlbumPage(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/album');
        self::assertResponseIsSuccessful();

        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        foreach ($albums as $album) {
            $albumName = $album->getName();
            self::assertNotNull($albumName);
            self::assertAnySelectorTextContains('td', $albumName);
        }
    }

    public function testAddAlbum(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/admin/album/add');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'album[name]' => 'testAddAlbum',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/album');

        $this->client->followRedirect();

        $album = $this->entityManager->getRepository(Album::class)->findOneBy([
            'name' => 'testAddAlbum',
        ]);

        self::assertNotNull($album);
        self::assertAnySelectorTextContains('td', 'testAddAlbum');
    }

    public function testEditAlbum(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $album = new Album();
        $album->setName('OldName');
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/admin/album/update/' . $album->getId());
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form([
            'album[name]' => 'NewName',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/album');

        $updatedAlbum = $this->entityManager->getRepository(Album::class)->findOneBy([
            'name' => 'NewName',
        ]);
        self::assertNotNull($updatedAlbum);
        self::assertSame('NewName', $updatedAlbum->getName());
    }

    public function testDeleteAlbum(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $album = new Album();
        $album->setName('To Be Deleted');
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $albumId = $album->getId();

        $this->client->request('GET', '/admin/album/delete/' . $albumId);
        self::assertResponseRedirects('/admin/album');

        self::assertNull($this->entityManager->getRepository(Album::class)->find($albumId));
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();

        parent::tearDown();
    }
}