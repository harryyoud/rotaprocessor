<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(options: ["default" => 0])]
    private int $maxInvites = 0;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: SyncJob::class)]
    private ?Collection $jobs = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Placement::class)]
    private ?Collection $placements = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: WebDavCalendar::class)]
    private ?Collection $calendars = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Invite::class)]
    private ?Collection $invites = null;

    public function __construct() {
        $this->jobs = new ArrayCollection();
        $this->placements = new ArrayCollection();
        $this->calendars = new ArrayCollection();
        $this->invites = new ArrayCollection();
    }

    public function getId(): ?Uuid {
        return $this->id;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string {
        return (string)$this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): self {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials() {
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;

        return $this;
    }

    /**
     * @return SyncJob[]
     */
    public function getJobs(): array {
        return $this->jobs->toArray();
    }

    /**
     * @return Placement[]
     */
    public function getPlacements(): array {
        return $this->placements->toArray();
    }

    /**
     * @return WebDavCalendar[]
     */
    public function getCalendars(): array {
        return $this->calendars->toArray();
    }

    public function isAdmin(): bool {
        return in_array("ROLE_ADMIN", $this->getRoles());
    }

    public function getRoles(): array {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Invite[]
     */
    public function getInvites(): array {
        return $this->invites->toArray();
    }

    public function getMaxInvites(): int {
        return $this->maxInvites;
    }

    public function setMaxInvites(int $maxInvites): void {
        $this->maxInvites = $maxInvites;
    }
}
