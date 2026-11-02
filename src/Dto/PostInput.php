<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Le groupe « full » (POST, PUT) rend les champs obligatoires ; PATCH ne valide que ceux envoyés.
 */
final readonly class PostInput
{
    public function __construct(
        #[Assert\NotNull(groups: ['full'])]
        #[Assert\Positive]
        public ?int $userId = null,

        #[Assert\NotNull(groups: ['full'])]
        #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
        #[Assert\Length(max: 255)]
        public ?string $title = null,

        #[Assert\NotNull(groups: ['full'])]
        #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
        #[Assert\Length(max: 5000)]
        public ?string $body = null,
    ) {
    }
}
