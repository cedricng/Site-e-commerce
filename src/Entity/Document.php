<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $documentFileName;



    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedAt;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocumentFileName(): ?string
    {
        return $this->documentFileName;
    }

    public function setDocumentFileName(string $documentFileName): self
    {
        $this->documentFileName = $documentFileName;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
