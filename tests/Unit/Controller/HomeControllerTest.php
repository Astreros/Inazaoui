<?php

namespace App\Tests\Unit\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class HomeControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
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
        $this->client->request('GET', '/guest/9999');
        self::assertResponseRedirects('/guests');
    }

    /**
     * @dataProvider provideRestrictedUser
     */
    public function testGuestPageRedirectsWhenGuestIsRestricted(User $restrictedUser): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($restrictedUser, $restrictedUser->getPassword());
        $restrictedUser->setPassword($hashedPassword);

        $this->entityManager->persist($restrictedUser);
        $this->entityManager->flush();

        $this->client->request('GET', '/guest/' . $restrictedUser->getId());
        self::assertResponseRedirects('/guests');
    }

    /**
     * @dataProvider provideUser
     */
    public function testGuestPage(User $user): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request('GET', '/guest/' . $user->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', $user->getUsername());
    }

    public function testAboutPage(): void
    {
        $this->client->request('GET', '/about');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Qui suis-je ?');
    }

    public static function provideRestrictedUser(): iterable
    {
        $restrictedUser = (new User())
        ->setUsername('restrictedUser' . md5(uniqid('', true)))
        ->setEmail('restrictedUser@example.com')
        ->setDescription('Restricted user')
        ->setRoles(['ROLE_USER'])
        ->setAdmin(false)
        ->setPassword('password')
        ->setRestricted(true);

        yield 'Restricted user' => [$restrictedUser];
    }

    public static function provideUser(): iterable
    {
        $user = (new User())
            ->setUsername('user' . md5(uniqid('', true)))
            ->setEmail('user@example.com')
            ->setDescription('User')
            ->setRoles(['ROLE_USER'])
            ->setAdmin(false)
            ->setPassword('password')
            ->setRestricted(false);

        yield 'User' => [$user];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}