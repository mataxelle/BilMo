<?php

namespace App\Entity\Traits;

use App\Entity\Client;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;

trait BlameableEntity
{
    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[Gedmo\Blameable(on: 'create')]
    #[Groups(['user:read'])]
    protected ?Client $createdBy = null;

    #[ORM\Column(nullable: true)]
    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[Gedmo\Blameable(on: 'update')]
    #[Groups(['user:read'])]
    protected ?Client $updatedBy = null;

    /**
     * Sets createdBy.
     *
     * @param Client $createdBy CreatedBy
     *
     * @return $this
     */
    public function setCreatedBy(?Client $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Returns createdBy.
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Sets updatedBy.
     *
     * @param Client $updatedBy UpdatedBy
     *
     * @return $this
     */
    public function setUpdatedBy(?Client $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Returns updatedBy.
     *
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
