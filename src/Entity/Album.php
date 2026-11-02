<?php

namespace App\Entity;

use App\Dto\AlbumInput;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'albums')]
#[ORM\Index(name: 'idx_albums_sandbox_cleanup', columns: ['is_seed', 'created_at'])]
class Album
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Ignore]
    public private(set) User $user;

    public function update(AlbumInput $input, ?User $user): void
    {
        $this->user = $user ?? $this->user;
        $this->title = $input->title ?? $this->title;
    }
}
