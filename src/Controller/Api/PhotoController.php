<?php

namespace App\Controller\Api;

use App\Dto\PhotoInput;
use App\Entity\Album;
use App\Entity\Photo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/photos', name: 'api_photos_', format: 'json')]
final class PhotoController extends AbstractResourceController
{
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] PhotoInput $input): JsonResponse
    {
        return $this->json($this->write(new Photo(), $input, false), 201);
    }

    #[Route('/{id}', name: 'replace', requirements: ['id' => self::ID], methods: ['PUT'])]
    public function replace(int $id, #[MapRequestPayload(validationGroups: self::FULL_VALIDATION)] PhotoInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(Photo::class, $id), $input, false));
    }

    #[Route('/{id}', name: 'patch', requirements: ['id' => self::ID], methods: ['PATCH'])]
    public function patch(int $id, #[MapRequestPayload] PhotoInput $input): JsonResponse
    {
        return $this->json($this->write($this->resources->get(Photo::class, $id), $input, true));
    }

    protected function resourceClass(): string
    {
        return Photo::class;
    }

    protected function filters(): array
    {
        return ['albumId' => 'album', 'title' => 'title'];
    }

    private function write(Photo $photo, PhotoInput $input, bool $partial): Photo
    {
        $photo->update($input, $this->resources->reference(Album::class, $input->albumId), $partial);
        $this->resources->save($photo);

        return $photo;
    }
}
