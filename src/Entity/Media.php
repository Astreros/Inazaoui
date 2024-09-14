<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\ManyToOne(targetEntity: User::class, fetch: "EAGER", inversedBy: "medias")]
//    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Album::class, fetch: "EAGER")]
    private ?Album $album = null;

    #[ORM\Column]
    private string $path;

    #[ORM\Column]
    private string $title;

    #[Assert\File(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/jpg","image/png"],
        maxSizeMessage: "Le fichier ne peut dépasser 2 Mo.",
        mimeTypesMessage: "Veuillez télécharger une image valide (JPG, JPEG, PNG)",
    )]
    private ?UploadedFile $file = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    private ?User $users = null;

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getUser(): ?User
//    {
//        return $this->user;
//    }
//
//    public function setUser(?User $user): void
//    {
//        $this->user = $user;
//    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): void
    {
        $this->album = $album;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): static
    {
        $this->users = $users;

        return $this;
    }
}
