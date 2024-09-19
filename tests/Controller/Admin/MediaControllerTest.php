<?php

namespace App\Tests\Controller\Admin;

use App\DataFixtures\AppFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class MediaControllerTest extends WebTestCase
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









    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }
}