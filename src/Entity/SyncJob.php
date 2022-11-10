<?php

namespace App\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class SyncJob {
    const STATUS_CREATED = 0;
    const STATUS_PENDING = 1;
    const STATUS_FAILED  = 2;
    const STATUS_SUCCESS = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\ManyToOne(targetEntity: Placement::class, inversedBy: 'jobs')]
    private ?Placement $placement = null;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $log = "";

    #[ORM\Column(length: 255)]
    private string $filename = "";

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'jobs')]
    private ?User $owner = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $createdAt = null;

    public function __construct(Placement $placement, string $filename) {
        $this->placement = $placement;
        $this->filename = $filename;
        $this->status = static::STATUS_CREATED;
    }

    public function markPending(): void {
        $this->status = static::STATUS_PENDING;
    }

    public function markSuccess(string $log): void {
        $this->status = static::STATUS_SUCCESS;
        $this->log = $log;
    }

    public function markFailed(string $log): void {
        $this->status = static::STATUS_FAILED;
        $this->log = $log;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int {
        return $this->status;
    }

    /**
     * @return Placement|null
     */
    public function getPlacement(): ?Placement {
        return $this->placement;
    }

    /**
     * @return string
     */
    public function getLog(): string {
        return $this->log;
    }

    /**
     * @return string
     */
    public function getFilename(): string {
        return $this->filename;
    }

    public function getStatusAsString(): string {
        return match($this->status) {
            static::STATUS_CREATED => 'Awaiting processing',
            static::STATUS_PENDING => 'Processing',
            static::STATUS_SUCCESS => 'Success',
            static::STATUS_FAILED => 'Failed',
        };
    }
    /**
     * @return User|null
     */
    public function getOwner(): ?User {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     */
    public function setOwner(?User $owner): void {
        $this->owner = $owner;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void {
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable {
        return $this->createdAt;
    }

}