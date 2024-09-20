<?php

namespace App\Tests\Controller\Admin;

use App\DataFixtures\AppFixtures;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GuestControllerTest extends WebTestCase
{
    private KernelBrowser $client;
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

    public function testIndexGuestPage(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/guest');
        self::assertResponseIsSuccessful();

        $guests = $this->entityManager->getRepository(User::class)->findBy([
            'admin' => false,
        ]);

        foreach ($guests as $guest) {
            self::assertAnySelectorTextContains('td', $guest->getUsername());
        }
    }

    public function testAddGuest(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/admin/guest/add');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'guest[username]' => 'itsuki ',
            'guest[email]' => 'itsuki@mail.com',
            'guest[password][first]' => 'password',
            'guest[password][second]' => 'password',
            'guest[description]' =>'Guest description',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/guest');

        $guest = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'itsuki@mail.com',
        ]);
        self::assertNotNull($guest);
        self::assertSame('itsuki@mail.com', $guest->getEmail());
    }

    public function testBlockGuest(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        $guest = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => false,
            'restricted' => false,
        ]);

        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/guest/block/' . $guest->getId());
        self::assertResponseRedirects('/admin/guest');

        $updatedGuest = $this->entityManager->getRepository(User::class)->find($guest->getId());
        self::assertTrue($updatedGuest->isRestricted());
    }

    public function testUnblockGuest(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        $guest = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => false,
            'restricted' => true,
        ]);

        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/guest/unblock/' . $guest->getId());
        self::assertResponseRedirects('/admin/guest');

        $updatedGuest = $this->entityManager->getRepository(User::class)->find($guest->getId());
        self::assertFalse($updatedGuest->isRestricted());
    }

    public function testDeleteGuest(): void
    {
        $user = new User();
        $user->setEmail('itsuki@mail.com');
        $user->setUsername('itsuki');
        $user->setPassword('password');
        $user->setDescription('Guest description');
        $user->setAdmin(false);
        $user->setRestricted(false);
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $userId = $user->getId();
        self::assertNotNull($userId);

        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/guest/delete/' . $user->getId());
        self::assertResponseRedirects('/admin/guest');

        $deletedGuest = $this->entityManager->getRepository(User::class)->find($userId);
        self::assertNull($deletedGuest);
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }
}