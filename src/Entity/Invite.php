<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class Invite {
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(nullable: false)]
    private bool $used = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'invites')]
    private ?User $owner = null;

    #[ORM\Column(nullable: true)]
    private ?string $emailUsed = null;

    #[ORM\Column(nullable: false)]
    private ?string $comment = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $usedAt = null;


    public function getId(): ?Uuid {
        return $this->id;
    }

    public function isUsed(): bool {
        return $this->used;
    }

    public function setUsed(bool $used): void {
        $this->used = $used;
    }

    public function getCreatedAt(): ?DateTimeImmutable {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function getEmailUsed(): ?string {
        return $this->emailUsed;
    }

    public function setEmailUsed(?string $emailUsed): void {
        $this->emailUsed = $emailUsed;
    }

    public function getUsedAt(): ?DateTimeImmutable {
        return $this->usedAt;
    }

    public function setUsedAt(?DateTimeImmutable $usedAt): void {
        $this->usedAt = $usedAt;
    }

    public function getComment(): ?string {
        return $this->comment;
    }

    public function setComment(?string $comment): void {
        $this->comment = $comment;
    }

    public function getOwner(): ?User {
        return $this->owner;
    }

    public function setOwner(?User $owner): void {
        $this->owner = $owner;
    }


}