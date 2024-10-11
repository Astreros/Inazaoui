<?php

namespace App\Repository;

use App\Entity\Album;
use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * @return Media[] Returns an array of Media objects
     * @phpstan-return array<Media>
     */
    public function findAllMediasNotRestricted(): array
    {
        return $this->createQueryBuilder('media')
            ->join('media.user', 'user')
            ->where('user.restricted = false')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Album $album
     * @return Media[] Returns an array of Media objects
     */
    public function findAllMediasNotRestrictedByAlbum(Album $album): array
    {
        return $this->createQueryBuilder('media')
            ->join('media.user', 'user')
            ->where('user.restricted = false')
            ->andWhere('media.album = :album')
            ->setParameter('album', $album)
            ->getQuery()
            ->getResult();
    }
}
