<?php

namespace App\Entity;

use App\Dto\PostInput;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'posts')]
#[ORM\Index(name: 'idx_posts_sandbox_cleanup', columns: ['is_seed', 'created_at'])]
class Post
{
    use SandboxEntityTrait;

    public int $userId {
        get => $this->user->id;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    public private(set) string $title;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $body;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Ignore]
    public private(set) User $user;

    public function update(PostInput $input, ?User $user): void
    {
        $this->user = $user ?? $this->user;
        $this->title = $input->title ?? $this->title;
        $this->body = $input->body ?? $this->body;
    }
}
