<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PhotoInput
{
    public function __construct(
        #[Assert\NotNull(groups: ['full'])]
        #[Assert\Positive]
        public ?int $albumId = null,

        #[Assert\NotNull(groups: ['full'])]
        #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
        #[Assert\Length(max: 255)]
        public ?string $title = null,

        #[Assert\Url(requireTld: true)]
        #[Assert\Length(max: 500)]
        public ?string $url = null,

        #[Assert\Url(requireTld: true)]
        #[Assert\Length(max: 500)]
        public ?string $thumbnailUrl = null,
    ) {
    }
}
