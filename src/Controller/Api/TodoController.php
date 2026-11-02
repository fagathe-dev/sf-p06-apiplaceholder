<?php

namespace App\Controller\Api;

use App\Dto\TodoInput;
use App\Entity\Todo;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/todos', name: 'api_todos_', format: 'json')]
final class TodoController extends AbstractResourceController
{
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] TodoInput $input): JsonResponse
    {
        return $this->json($this->write(new Todo(), $input, false), 201);
    }

    #[Route('/{id}', name: 'replace', requirements: ['id' => self::ID], methods: ['PUT'])]
    public function replace(int $id, #[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] TodoInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(Todo::class, $id), $input, false));
    }

    #[Route('/{id}', name: 'patch', requirements: ['id' => self::ID], methods: ['PATCH'])]
    public function patch(int $id, #[MapRequestPayload] TodoInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(Todo::class, $id), $input, true));
    }

    protected function resourceClass(): string
    {
        return Todo::class;
    }

    protected function filters(): array
    {
        return ['userId' => 'user', 'title' => 'title', 'completed' => 'completed'];
    }

    private function write(Todo $todo, TodoInput $input, bool $partial): Todo
    {
        $todo->update($input, $this->resources->reference(User::class, $input->userId), $partial);
        $this->resources->save($todo);

        return $todo;
    }
}
