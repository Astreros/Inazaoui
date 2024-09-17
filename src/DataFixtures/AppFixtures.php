<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un utilisateur avec ROLE_ADMIN
        $adminUser = new User();
        $adminUser->setUsername('ina');
        $adminUser->setEmail('ina@mail.com');
        $adminUser->setDescription('Description utilisateur Ina');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setAdmin(true);
        $adminUser->setPassword($this->userPasswordHasher->hashPassword($adminUser, 'password'));
        $manager->persist($adminUser);

        // Création d'un utilisateur avec ROLE_USER
        $user = new User();
        $user->setUsername('akira');
        $user->setEmail('akira@mail.com');
        $user->setDescription('Description utilisateur Akira');
        $user->setRoles(['ROLE_USER']);
        $user->setAdmin(false);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        // Création albums
        $albumNature = new Album();
        $albumNature->setName('Nature');
        $manager->persist($albumNature);

        $albumVilles = new Album();
        $albumVilles->setName('Villes');
        $manager->persist($albumVilles);

        $albumJapon = new Album();
        $albumJapon->setName('Japon');
        $manager->persist($albumJapon);

        // Création médias pour l'album Nature
        $natureMediaPaths = [
            "uploads/nature-8162195_fixtures.webp",
            "uploads/nature-8585535_fixtures.webp",
            "uploads/nature-8910009_fixtures.webp",
        ];

        foreach ($natureMediaPaths as $index => $path) {
            $media = new Media();
            $media->setPath($path);
            $media->setTitle('Nature ' . ($index + 1));
            $media->setAlbum($albumNature);
            $media->setUser($adminUser);
            $manager->persist($media);
        }

        // Création médias pour l'album Ville
        $villeMediaPaths = [
            "uploads/ville-6528079_fixtures.webp",
            "uploads/ville-7095262_fixtures.webp",
        ];

        foreach ($villeMediaPaths as $index => $path) {
            $media = new Media();
            $media->setPath($path);
            $media->setTitle('Ville ' . ($index + 1));
            $media->setAlbum($albumVilles);
            $media->setUser($user);
            $manager->persist($media);
        }

        // Création médias pour l'album Japon
        $japonMediaPaths = [
            "uploads/japon-2014616_fixtures.webp",
            "uploads/japon-6683245_fixtures.webp",
            "uploads/japon-6876155_fixtures.webp",
            "uploads/japon-7387131_fixtures.webp",
        ];

        foreach ($japonMediaPaths as $index => $path) {
            $media = new Media();
            $media->setPath($path);
            $media->setTitle('Japon ' . ($index + 1));
            $media->setAlbum($albumJapon);
            $media->setUser($user);
            $manager->persist($media);
        }

        $manager->flush();
    }
}

