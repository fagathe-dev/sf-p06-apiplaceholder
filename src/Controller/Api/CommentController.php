<?php

namespace App\Controller\Api;

use App\Dto\CommentInput;
use App\Entity\Comment;
use App\Entity\Post;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/comments', name: 'api_comments_', format: 'json')]
final class CommentController extends AbstractResourceController
{
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] CommentInput $input): JsonResponse
    {
        return $this->json($this->write(new Comment(), $input, false), 201);
    }

    #[Route('/{id}', name: 'replace', requirements: ['id' => self::ID], methods: ['PUT'])]
    public function replace(int $id, #[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] CommentInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(Comment::class, $id), $input, false));
    }

    #[Route('/{id}', name: 'patch', requirements: ['id' => self::ID], methods: ['PATCH'])]
    public function patch(int $id, #[MapRequestPayload] CommentInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(Comment::class, $id), $input, true));
    }

    protected function resourceClass(): string
    {
        return Comment::class;
    }

    protected function filters(): array
    {
        return ['postId' => 'post', 'name' => 'name', 'email' => 'email'];
    }

    private function write(Comment $comment, CommentInput $input, bool $partial): Comment
    {
        $comment->update($input, $this->resources->reference(Post::class, $input->postId), $partial);
        $this->resources->save($comment);

        return $comment;
    }
}
