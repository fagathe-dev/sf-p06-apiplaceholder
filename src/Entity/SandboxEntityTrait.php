<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

/**
 * Métadonnées techniques de la sandbox, jamais exposées dans le JSON.
 */
trait SandboxEntityTrait
{
    #[ORM\Column(options: ['default' => false])]
    #[Ignore]
    public private(set) bool $isSeed = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Ignore]
    public private(set) \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function markAsSeed(): static
    {
        $this->isSeed = true;

        return $this;
    }
}
