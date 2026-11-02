<?php

namespace App\Controller\Api;

use App\Dto\PageQuery;
use App\Sandbox\ResourceManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Lecture et suppression communes aux six ressources. Les écritures restent dans chaque
 * contrôleur, car #[MapRequestPayload] a besoin du type exact du payload.
 */
abstract class AbstractResourceController extends AbstractController
{
    protected const ID = '[1-9]\d{0,8}';
    protected const FULL_VALIDATION = ['Default', 'full'];

    public function __construct(protected readonly ResourceManager $resources) {}

    /**
     * @return class-string
     */
    abstract protected function resourceClass(): string;

    /**
     * @return array<string, string> paramètre de query string => champ de l'entité
     */
    abstract protected function filters(): array;

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        #[MapQueryString(validationFailedStatusCode: 422)] PageQuery $page = new PageQuery(),
    ): JsonResponse {
        $criteria = $this->resources->criteria($this->resourceClass(), $this->filters(), $request->query->all());

        return $this->json(null === $criteria ? [] : $this->resources->list($this->resourceClass(), $criteria, $page));
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => self::ID], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        return $this->json($this->resources->get($this->resourceClass(), $id));
    }

    /**
     * Faux delete : l'API répond comme si la suppression avait eu lieu, sans toucher à la base.
     */
    #[Route('/{id}', name: 'delete', requirements: ['id' => self::ID], methods: ['DELETE'])]
    public function delete(): Response
    {
        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
