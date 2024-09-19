<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private mixed $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get(null);

        $databaseTool->loadFixtures([
            AppFixtures::class
        ]);
    }

    public function testHomePage(): void
    {
        $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Photographe');
    }

    public function testGuestsPage(): void
    {
        $this->client->request('GET', '/guests');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', 'Invités');
    }

    public function testGuestPageRedirectsGuestNotFound(): void
    {
        $this->client->request('GET', '/guest/99999999');
        self::assertResponseRedirects('/guests');
    }

    public function testGuestPageRedirectsWhenGuestIsRestricted(): void
    {
        $guestRestricted = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => false,
            'restricted' => true,
        ]);

        $this->client->request('GET', '/guest/' . $guestRestricted->getId());
        self::assertResponseRedirects('/guests');
    }

    public function testGuestPage(): void
    {
        $guest = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => false,
            'restricted' => false,
        ]);

        $this->client->request('GET', '/guest/' . $guest->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', $guest->getUsername());
    }

    public function testPortfolioPageWithoutId(): void
    {
        $this->client->request('GET', '/portfolio');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', 'Portfolio');

        $albums = $this->entityManager->getRepository(Album::class)->findAll();

        foreach ($albums as $album) {
            self::assertSelectorExists('a[href="/portfolio/' . $album->getId() . '"]');
        }

        $medias = $this->entityManager->getRepository(Media::class)->findAllMediasNotRestricted();

        foreach ($medias as $media) {
            self::assertSelectorExists('img[src="/' . $media->getPath() . '"]');
        }
    }

    public function testPortfolioPageWithId(): void
    {
        $albums = $this->entityManager->getRepository(Album::class)->findAll();

        foreach ($albums as $album) {
            $medias = $this->entityManager->getRepository(Media::class)->findAllMediasNotRestrictedByAlbum($album);

            $this->client->request('GET', '/portfolio/' . $album->getId());

            self::assertResponseIsSuccessful();

            foreach ($medias as $media) {
                self::assertSelectorExists('img[src="/' . $media->getPath() . '"]');
            }
        }
    }

    public function testAboutPage(): void
    {
        $this->client->request('GET', '/about');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Qui suis-je ?');
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }
}