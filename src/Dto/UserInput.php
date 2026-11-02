<?php

namespace App\Dto;

use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Company;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserInput
{
    public function __construct(
        #[Assert\NotNull(groups: ['full'])]
        #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
        #[Assert\Length(max: 255)]
        public ?string $name = null,

        #[Assert\Length(max: 255)]
        public ?string $username = null,

        #[Assert\Email]
        #[Assert\Length(max: 255)]
        public ?string $email = null,

        #[Assert\Valid]
        public ?Address $address = null,

        #[Assert\Length(max: 255)]
        public ?string $phone = null,

        #[Assert\Length(max: 255)]
        public ?string $website = null,

        #[Assert\Valid]
        public ?Company $company = null,
    ) {
    }
}
