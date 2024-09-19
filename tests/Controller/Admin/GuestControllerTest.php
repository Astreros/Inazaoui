<?php

namespace App\Tests\Controller\Admin;

use App\DataFixtures\AppFixtures;
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

    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }
}