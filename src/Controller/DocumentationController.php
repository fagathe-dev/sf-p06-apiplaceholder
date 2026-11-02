<?php

namespace App\Controller;

use App\Documentation\ApiCatalog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/docs', name: 'app_docs_', methods: ['GET'])]
final class DocumentationController extends AbstractController
{
    public function __construct(private readonly ApiCatalog $catalog)
    {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('docs/index.html.twig', [
            'resources' => $this->catalog->all(),
            'presets' => $this->catalog->presets(),
        ]);
    }

    #[Route('/{slug}', name: 'resource')]
    public function resource(string $slug): Response
    {
        $resource = $this->catalog->find($slug) ?? throw $this->createNotFoundException();

        return $this->render('docs/resource.html.twig', [
            'resources' => $this->catalog->all(),
            'resource' => $resource,
            'presets' => $this->catalog->resourcePresets($resource),
        ]);
    }
}
