<?php

namespace App\Controller\Api;

use App\Dto\PageQuery;
use App\Dto\PostInput;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/posts', name: 'api_posts_', format: 'json')]
final class PostController extends AbstractResourceController
{
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] PostInput $input): JsonResponse
    {
        return $this->json($this->write(new Post(), $input), 201);
    }

    #[Route('/{id}', name: 'replace', requirements: ['id' => self::ID], methods: ['PUT'])]
    public function replace(int $id, #[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] PostInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(Post::class, $id), $input));
    }

    #[Route('/{id}', name: 'patch', requirements: ['id' => self::ID], methods: ['PATCH'])]
    public function patch(int $id, #[MapRequestPayload] PostInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(Post::class, $id), $input));
    }

    #[Route('/{id}/comments', name: 'comments', requirements: ['id' => self::ID], methods: ['GET'])]
    public function comments(int $id, #[MapQueryString(validationFailedStatusCode: 422)] PageQuery $page = new PageQuery()): JsonResponse
    {
        return $this->json($this->resources->list(Comment::class, ['post' => $id], $page));
    }

    protected function resourceClass(): string
    {
        return Post::class;
    }

    protected function filters(): array
    {
        return ['userId' => 'user', 'title' => 'title'];
    }

    private function write(Post $post, PostInput $input): Post
    {
        $post->update($input, $this->resources->reference(User::class, $input->userId));
        $this->resources->save($post);

        return $post;
    }
}
