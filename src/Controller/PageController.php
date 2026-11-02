<?php

namespace App\Controller;

use App\Documentation\ApiCatalog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(ApiCatalog $catalog): Response
    {
        return $this->render('pages/home.html.twig', [
            'resources' => $catalog->all(),
            'presets' => $catalog->presets(),
        ]);
    }

    /**
     * @deprecated remplacée par la documentation : l'URL ne reste que pour les anciens liens
     */
    #[Route('/guide', name: 'app_guide', methods: ['GET'])]
    public function guide(): RedirectResponse
    {
        return $this->redirectToRoute('app_docs_index', status: Response::HTTP_MOVED_PERMANENTLY);
    }
}
