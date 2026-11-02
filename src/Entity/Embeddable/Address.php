<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
final readonly class Address
{
    public function __construct(
        #[ORM\Column(length: 255, nullable: true)]
        #[Assert\Length(max: 255)]
        public ?string $street = null,

        #[ORM\Column(length: 255, nullable: true)]
        #[Assert\Length(max: 255)]
        public ?string $suite = null,

        #[ORM\Column(length: 255, nullable: true)]
        #[Assert\Length(max: 255)]
        public ?string $city = null,

        #[ORM\Column(length: 32, nullable: true)]
        #[Assert\Length(max: 32)]
        public ?string $zipcode = null,

        #[ORM\Embedded]
        #[Assert\Valid]
        public Geo $geo = new Geo(),
    ) {
    }
}
