<?php

namespace App\Entity;

use App\Dto\PhotoInput;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'photos')]
#[ORM\Index(name: 'idx_photos_sandbox_cleanup', columns: ['is_seed', 'created_at'])]
class Photo
{
    use SandboxEntityTrait;

    public int $albumId {
        get => $this->album->id;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    public private(set) string $title;

    #[ORM\Column(length: 500, nullable: true)]
    public private(set) ?string $url = null;

    #[ORM\Column(length: 500, nullable: true)]
    public private(set) ?string $thumbnailUrl = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Ignore]
    public private(set) Album $album;

    public function update(PhotoInput $input, ?Album $album, bool $partial): void
    {
        $this->album = $album ?? $this->album;
        $this->title = $input->title ?? $this->title;
        $this->url = $input->url ?? ($partial ? $this->url : null);
        $this->thumbnailUrl = $input->thumbnailUrl ?? ($partial ? $this->thumbnailUrl : null);
    }
}
