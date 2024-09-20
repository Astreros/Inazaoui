<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
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

    public function testLoginPage(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion');
    }

    public function testLoginRedirectsAuthenticatedUser(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'restricted' => false
        ]);

        $this->client->loginUser($user);
        $this->client->request('GET', '/login');
        self::assertResponseRedirects('/');

        $this->client->followRedirect();
        self::assertSelectorExists('a[href="/logout"]');
    }

    public function testRestrictedUserCannotLogin(): void
    {
        $restrictedUser = $this->entityManager->getRepository(User::class)->findOneBy([
            'restricted' => true
        ]);

        $this->client->request('POST', '/login', [
            'username' => $restrictedUser->getUsername(),
            'password' => 'password'
        ]);

        $this->client->followRedirect();
        self::assertSelectorTextContains('div', 'Votre compte est restreint.');
    }

    public function testLogoutRedirectsUser(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'restricted' => false
        ]);

        $this->client->loginUser($user);
        $this->client->request('GET', '/logout');
        self::assertResponseRedirects('/');

        $this->client->followRedirect();
        self::assertSelectorExists('a[href="/login"]');
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }
}