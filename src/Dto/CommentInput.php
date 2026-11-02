<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CommentInput
{
    public function __construct(
        #[Assert\NotNull(groups: ['full'])]
        #[Assert\Positive]
        public ?int $postId = null,

        #[Assert\NotNull(groups: ['full'])]
        #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
        #[Assert\Length(max: 255)]
        public ?string $name = null,

        #[Assert\Email]
        #[Assert\Length(max: 255)]
        public ?string $email = null,

        #[Assert\NotNull(groups: ['full'])]
        #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
        #[Assert\Length(max: 5000)]
        public ?string $body = null,
    ) {
    }
}
