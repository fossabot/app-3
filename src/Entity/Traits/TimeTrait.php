<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/*
 * automatically keeps track of creation time & last change time
 */

trait TimeTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $lastChangedAt;

    /**
     * @ORM\PrePersist()
     */
    public function prePersistTime()
    {
        $this->createdAt = new \DateTime();
        $this->lastChangedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdateTime()
    {
        $this->lastChangedAt = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getLastChangedAt()
    {
        return $this->lastChangedAt;
    }
}
