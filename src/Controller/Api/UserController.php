<?php

namespace App\Controller\Api;

use App\Dto\PageQuery;
use App\Dto\UserInput;
use App\Entity\Album;
use App\Entity\Post;
use App\Entity\Todo;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', name: 'api_users_', format: 'json')]
final class UserController extends AbstractResourceController
{
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] UserInput $input): JsonResponse
    {
        return $this->json($this->write(new User(), $input, false), 201);
    }

    #[Route('/{id}', name: 'replace', requirements: ['id' => self::ID], methods: ['PUT'])]
    public function replace(int $id, #[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] UserInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(User::class, $id), $input, false));
    }

    #[Route('/{id}', name: 'patch', requirements: ['id' => self::ID], methods: ['PATCH'])]
    public function patch(int $id, #[MapRequestPayload] UserInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(User::class, $id), $input, true));
    }

    #[Route('/{id}/albums', name: 'albums', requirements: ['id' => self::ID], methods: ['GET'])]
    public function albums(int $id, #[MapQueryString(validationFailedStatusCode: 422)] PageQuery $page = new PageQuery()): JsonResponse
    {
        return $this->json($this->resources->list(Album::class, ['user' => $id], $page));
    }

    #[Route('/{id}/todos', name: 'todos', requirements: ['id' => self::ID], methods: ['GET'])]
    public function todos(int $id, #[MapQueryString(validationFailedStatusCode: 422)] PageQuery $page = new PageQuery()): JsonResponse
    {
        return $this->json($this->resources->list(Todo::class, ['user' => $id], $page));
    }

    #[Route('/{id}/posts', name: 'posts', requirements: ['id' => self::ID], methods: ['GET'])]
    public function posts(int $id, #[MapQueryString(validationFailedStatusCode: 422)] PageQuery $page = new PageQuery()): JsonResponse
    {
        return $this->json($this->resources->list(Post::class, ['user' => $id], $page));
    }

    protected function resourceClass(): string
    {
        return User::class;
    }

    protected function filters(): array
    {
        return ['name' => 'name', 'username' => 'username', 'email' => 'email'];
    }

    private function write(User $user, UserInput $input, bool $partial): User
    {
        $user->update($input, $partial);
        $this->resources->save($user);

        return $user;
    }
}
