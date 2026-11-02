<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class TodoInput
{
    public function __construct(
        #[Assert\NotNull(groups: ['full'])]
        #[Assert\Positive]
        public ?int $userId = null,

        #[Assert\NotNull(groups: ['full'])]
        #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
        #[Assert\Length(max: 255)]
        public ?string $title = null,

        public ?bool $completed = null,
    ) {
    }
}
