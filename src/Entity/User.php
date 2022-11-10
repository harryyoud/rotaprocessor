<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: SyncJob::class)]
    private ?Collection $jobs = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Placement::class)]
    private ?Collection $placements = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: WebDavCalendar::class)]
    private ?Collection $calendars = null;

    public function __construct() {
        $this->jobs = new ArrayCollection();
        $this->placements = new ArrayCollection();
        $this->calendars = new ArrayCollection();
    }

    public function getId(): ?int {
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
        return (string) $this->email;
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
}
