<?php

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;

trait BlameableEntity
{
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Gedmo\Blameable(on: 'create')]
    #[Groups(['member:read'])]
    protected ?User $createdBy = null;

    #[ORM\Column(nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Gedmo\Blameable(on: 'update')]
    #[Groups(['member:read'])]
    protected ?User $updatedBy = null;

    /**
     * Sets createdBy.
     *
     * @param User $createdBy CreatedBy
     *
     * @return $this
     */
    public function setCreatedBy(?User $createdBy): self
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
     * @param User $updatedBy UpdatedBy
     *
     * @return $this
     */
    public function setUpdatedBy(?User $updatedBy): self
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
