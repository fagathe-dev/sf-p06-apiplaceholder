<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class PageQuery
{
    public function __construct(
        #[SerializedName('_start')]
        #[Assert\PositiveOrZero]
        public int $start = 0,

        #[SerializedName('_limit')]
        #[Assert\Positive]
        public ?int $limit = null,
    ) {
    }
}
