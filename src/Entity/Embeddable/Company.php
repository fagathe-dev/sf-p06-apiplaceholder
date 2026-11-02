<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
final readonly class Company
{
    public function __construct(
        #[ORM\Column(length: 255, nullable: true)]
        #[Assert\Length(max: 255)]
        public ?string $name = null,

        #[ORM\Column(length: 255, nullable: true)]
        #[Assert\Length(max: 255)]
        public ?string $catchPhrase = null,

        #[ORM\Column(length: 255, nullable: true)]
        #[Assert\Length(max: 255)]
        public ?string $bs = null,
    ) {
    }
}
