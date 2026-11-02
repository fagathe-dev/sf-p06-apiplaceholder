<?php

namespace App\Entity;

use App\Dto\UserInput;
use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Company;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\Index(name: 'idx_users_sandbox_cleanup', columns: ['is_seed', 'created_at'])]
class User
{
    use SandboxEntityTrait {
        SandboxEntityTrait::__construct as private initSandbox;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    public private(set) string $name;

    #[ORM\Column(length: 255, nullable: true)]
    public private(set) ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    public private(set) ?string $email = null;

    #[ORM\Embedded]
    public private(set) Address $address;

    #[ORM\Column(length: 255, nullable: true)]
    public private(set) ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    public private(set) ?string $website = null;

    #[ORM\Embedded]
    public private(set) Company $company;

    public function __construct()
    {
        $this->initSandbox();
        $this->address = new Address();
        $this->company = new Company();
    }

    public function update(UserInput $input, bool $partial): void
    {
        $this->name = $input->name ?? $this->name;
        $this->username = $input->username ?? ($partial ? $this->username : null);
        $this->email = $input->email ?? ($partial ? $this->email : null);
        $this->address = $input->address ?? ($partial ? $this->address : new Address());
        $this->phone = $input->phone ?? ($partial ? $this->phone : null);
        $this->website = $input->website ?? ($partial ? $this->website : null);
        $this->company = $input->company ?? ($partial ? $this->company : new Company());
    }
}
