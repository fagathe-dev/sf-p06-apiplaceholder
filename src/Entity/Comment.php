<?php

namespace App\Entity;

use App\Dto\CommentInput;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'comments')]
#[ORM\Index(name: 'idx_comments_sandbox_cleanup', columns: ['is_seed', 'created_at'])]
class Comment
{
    use SandboxEntityTrait;

    public int $postId {
        get => $this->post->id;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    public private(set) string $name;

    #[ORM\Column(length: 255, nullable: true)]
    public private(set) ?string $email = null;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $body;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Ignore]
    public private(set) Post $post;

    public function update(CommentInput $input, ?Post $post, bool $partial): void
    {
        $this->post = $post ?? $this->post;
        $this->name = $input->name ?? $this->name;
        $this->email = $input->email ?? ($partial ? $this->email : null);
        $this->body = $input->body ?? $this->body;
    }
}
