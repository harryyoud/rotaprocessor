<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Placement {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $processor = null;

    #[ORM\Column(length: 255)]
    private ?string $calendarCategory = null;

    #[ORM\Column(length: 255)]
    private ?string $prefix = null;

    #[ORM\Column(length: 255)]
    private ?string $nameFilter = null;

    #[ORM\Column(length: 255)]
    private ?string $sheetName = null;

    #[ORM\ManyToOne(targetEntity: WebDavCalendar::class)]
    private ?WebDavCalendar $calendar = null;

    #[ORM\OneToMany(mappedBy: 'placement', targetEntity: SyncJob::class)]
    private ?Collection $jobs = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $shifts = null;

    public function __construct() {
        $this->jobs = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;

        return $this;
    }

    public function getProcessor(): ?string {
        return $this->processor;
    }

    public function setProcessor(string $processor): self {
        $this->processor = $processor;

        return $this;
    }

    public function getCalendarCategory(): ?string {
        return $this->calendarCategory;
    }

    public function setCalendarCategory(string $calendarCategory): self {
        $this->calendarCategory = $calendarCategory;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrefix(): ?string {
        return $this->prefix;
    }

    /**
     * @param string|null $prefix
     */
    public function setPrefix(?string $prefix): void {
        $this->prefix = $prefix;
    }

    /**
     * @return WebDavCalendar|null
     */
    public function getCalendar(): ?WebDavCalendar {
        return $this->calendar;
    }

    /**
     * @param WebDavCalendar|null $calendar
     */
    public function setCalendar(?WebDavCalendar $calendar): void {
        $this->calendar = $calendar;
    }

    /**
     * @return string|null
     */
    public function getNameFilter(): ?string {
        return $this->nameFilter;
    }

    /**
     * @param string|null $nameFilter
     */
    public function setNameFilter(?string $nameFilter): void {
        $this->nameFilter = $nameFilter;
    }

    /**
     * @return string|null
     */
    public function getSheetName(): ?string {
        return $this->sheetName;
    }

    /**
     * @param string|null $sheetName
     */
    public function setSheetName(?string $sheetName): void {
        $this->sheetName = $sheetName;
    }

    /**
     * @return SyncJob[]|null $jobs
     */
    public function getJobs(): ?array {
        return $this->jobs->toArray();
    }

    /**
     * @return string|null
     */
    public function getShifts(): ?string {
        return $this->shifts;
    }

    /**
     * @param string|null $shifts
     */
    public function setShifts(?string $shifts): void {
        $this->shifts = $shifts;
    }
}
