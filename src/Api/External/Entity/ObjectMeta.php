<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class ObjectMeta
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $lastChangeTime;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLastChangeTime(): string
    {
        return $this->lastChangeTime;
    }

    /**
     * @param string $lastChangeTime
     */
    public function setLastChangeTime(string $lastChangeTime): void
    {
        $this->lastChangeTime = $lastChangeTime;
    }
}
