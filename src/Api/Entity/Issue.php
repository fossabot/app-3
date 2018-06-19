<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


use App\Api\Entity\Base\BaseEntity;

class Issue
{
    use BaseEntity;

    /**
     * @var int|null
     */
    private $number;

    /**
     * @var bool
     */
    private $isMarked;

    /**
     * @var bool
     */
    private $wasAddedWithClient;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $craftsman;

    /**
     * @var string|null
     */
    private $imageFilename;

    /**
     * @var IssueStatus
     */
    private $status;

    /**
     * @var IssuePosition|null
     */
    private $position;

    /**
     * @return int|null
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int|null $number
     */
    public function setNumber(?int $number): void
    {
        $this->number = $number;
    }

    /**
     * @return bool
     */
    public function isMarked(): bool
    {
        return $this->isMarked;
    }

    /**
     * @param bool $isMarked
     */
    public function setIsMarked(bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    /**
     * @return bool
     */
    public function isWasAddedWithClient(): bool
    {
        return $this->wasAddedWithClient;
    }

    /**
     * @param bool $wasAddedWithClient
     */
    public function setWasAddedWithClient(bool $wasAddedWithClient): void
    {
        $this->wasAddedWithClient = $wasAddedWithClient;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return null|string
     */
    public function getCraftsman(): ?string
    {
        return $this->craftsman;
    }

    /**
     * @param null|string $craftsman
     */
    public function setCraftsman(?string $craftsman): void
    {
        $this->craftsman = $craftsman;
    }

    /**
     * @return null|string
     */
    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    /**
     * @param null|string $imageFilename
     */
    public function setImageFilename(?string $imageFilename): void
    {
        $this->imageFilename = $imageFilename;
    }

    /**
     * @return IssueStatus
     */
    public function getStatus(): IssueStatus
    {
        return $this->status;
    }

    /**
     * @param IssueStatus $status
     */
    public function setStatus(IssueStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return IssuePosition|null
     */
    public function getPosition(): ?IssuePosition
    {
        return $this->position;
    }

    /**
     * @param IssuePosition|null $position
     */
    public function setPosition(?IssuePosition $position): void
    {
        $this->position = $position;
    }
}