<?php

namespace App\Entity;

use App\Dto\TodoInput;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'todos')]
#[ORM\Index(name: 'idx_todos_sandbox_cleanup', columns: ['is_seed', 'created_at'])]
class Todo
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

    #[ORM\Column(options: ['default' => false])]
    public private(set) bool $completed = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Ignore]
    public private(set) User $user;

    public function update(TodoInput $input, ?User $user, bool $partial): void
    {
        $this->user = $user ?? $this->user;
        $this->title = $input->title ?? $this->title;
        $this->completed = $input->completed ?? ($partial && $this->completed);
    }
}
