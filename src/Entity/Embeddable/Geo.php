<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
final readonly class Geo
{
    public function __construct(
        #[ORM\Column(length: 32, nullable: true)]
        #[Assert\Length(max: 32)]
        public ?string $lat = null,

        #[ORM\Column(length: 32, nullable: true)]
        #[Assert\Length(max: 32)]
        public ?string $lng = null,
    ) {
    }
}
