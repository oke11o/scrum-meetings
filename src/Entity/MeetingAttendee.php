<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeetingAttendeeRepository")
 */
class MeetingAttendee
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="guid", unique=true)
     * @Assert\Uuid
     */
    private $hash;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Meeting", inversedBy="attendees")
     */
    private $meeting;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="meetingAttendees")
     */
    private $user;

    /**
     * @ORM\Column(type="text")
     */
    private $whatYesterday = '';

    /**
     * @ORM\Column(type="text")
     */
    private $whatToday = '';

    /**
     * @ORM\Column(type="text")
     */
    private $whatProblem = '';

    public function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMeeting(): ?Meeting
    {
        return $this->meeting;
    }

    public function setMeeting(?Meeting $meeting): self
    {
        $this->meeting = $meeting;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getWhatYesterday(): ?string
    {
        return $this->whatYesterday;
    }

    public function setWhatYesterday(string $whatYesterday): self
    {
        $this->whatYesterday = $whatYesterday;

        return $this;
    }

    public function getWhatToday(): ?string
    {
        return $this->whatToday;
    }

    public function setWhatToday(string $whatToday): self
    {
        $this->whatToday = $whatToday;

        return $this;
    }

    public function getWhatProblem(): ?string
    {
        return $this->whatProblem;
    }

    public function setWhatProblem(string $whatProblem): self
    {
        $this->whatProblem = $whatProblem;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }
}
