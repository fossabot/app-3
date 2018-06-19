<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;


use App\Api\ApiSerializable;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\PublicAccessibleTrait;
use App\Entity\Traits\ThingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Email is a sent email to the specified receivers.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Building extends BaseEntity
{
    use IdTrait;
    use AddressTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $imageFileName;

    /**
     * @var ConstructionManager[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ConstructionManager", inversedBy="buildings")
     * @ORM\JoinTable(name="buildings_construction_managers")
     */
    private $constructionManagers;

    /**
     * @var Map[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Map", mappedBy="building")
     * @ORM\OrderBy({"name": "ASC"})
     */
    private $maps;

    /**
     * @var Craftsman[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Craftsman", mappedBy="building")
     * @ORM\OrderBy({"name": "ASC"})
     */
    private $craftsmen;

    /**
     * Building constructor.
     */
    public function __construct()
    {
        $this->constructionManagers = new ArrayCollection();
        $this->maps = new ArrayCollection();
        $this->craftsmen = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getImageFileName(): ?string
    {
        return $this->imageFileName;
    }

    /**
     * @param null|string $imageFileName
     */
    public function setImageFileName(?string $imageFileName): void
    {
        $this->imageFileName = $imageFileName;
    }

    /**
     * @return ConstructionManager[]|ArrayCollection
     */
    public function getConstructionManagers()
    {
        return $this->constructionManagers;
    }

    /**
     * @return Map[]|ArrayCollection
     */
    public function getMaps()
    {
        return $this->maps;
    }

    /**
     * @return Craftsman[]|ArrayCollection
     */
    public function getCraftsmen()
    {
        return $this->craftsmen;
    }
}
