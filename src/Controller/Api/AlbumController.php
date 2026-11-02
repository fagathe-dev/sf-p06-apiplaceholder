<?php

namespace App\Controller\Api;

use App\Dto\AlbumInput;
use App\Dto\PageQuery;
use App\Entity\Album;
use App\Entity\Photo;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/albums', name: 'api_albums_', format: 'json')]
final class AlbumController extends AbstractResourceController
{
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] AlbumInput $input): JsonResponse
    {
        return $this->json($this->write(new Album(), $input), 201);
    }

    #[Route('/{id}', name: 'replace', requirements: ['id' => self::ID], methods: ['PUT'])]
    public function replace(int $id, #[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] AlbumInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(Album::class, $id), $input));
    }

    #[Route('/{id}', name: 'patch', requirements: ['id' => self::ID], methods: ['PATCH'])]
    public function patch(int $id, #[MapRequestPayload] AlbumInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(Album::class, $id), $input));
    }

    #[Route('/{id}/photos', name: 'photos', requirements: ['id' => self::ID], methods: ['GET'])]
    public function photos(int $id, #[MapQueryString(validationFailedStatusCode: 422)] PageQuery $page = new PageQuery()): JsonResponse
    {
        return $this->json($this->resources->list(Photo::class, ['album' => $id], $page));
    }

    protected function resourceClass(): string
    {
        return Album::class;
    }

    protected function filters(): array
    {
        return ['userId' => 'user', 'title' => 'title'];
    }

    private function write(Album $album, AlbumInput $input): Album
    {
        $album->update($input, $this->resources->reference(User::class, $input->userId));
        $this->resources->save($album);

        return $album;
    }
}
