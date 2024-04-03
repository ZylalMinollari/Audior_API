<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
#[ApiResource]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Auditor::class)]
    #[ORM\JoinColumn(name: "auditor_id", referencedColumnName: "id")]
    private ?Auditor $auditor;

    #[ORM\ManyToOne(targetEntity: Job::class)]
    #[ORM\JoinColumn(name: "job_id", referencedColumnName: "id")]
    private ?Job $job;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $assignedDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $completionDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuditor(): ?Auditor
    {
        return $this->auditor;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function getAssignedDate(): ?\DateTimeInterface
    {
        return $this->assignedDate;
    }

    public function getCompletionDate(): ?\DateTimeInterface
    {
        return $this->completionDate;
    }

    public function setAuditor(?Auditor $auditor): self
    {
        $this->auditor = $auditor;
        return $this;
    }

    public function setJob(?Job $job): self
    {
        $this->job = $job;
        return $this;
    }

    public function setAssignedDate(?\DateTimeInterface $assignedDate): self
    {
        $this->assignedDate = $assignedDate;
        return $this;
    }

    public function setCompletionDate(?\DateTimeInterface $completionDate): self
    {
        $this->completionDate = $completionDate;
        return $this;
    }
}
