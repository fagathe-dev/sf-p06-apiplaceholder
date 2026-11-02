<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * Règles communes aux routes de l'API : limite par IP, taille du body et erreurs en JSON.
 */
final readonly class ApiRequestListener
{
    private const API_PATH = '#^/api(/|$)#';
    private const MAX_BODY_SIZE = 64 * 1024;

    public function __construct(private RateLimiterFactoryInterface $apiLimiter)
    {
    }

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 40)]
    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$this->isApiRequest($request)) {
            return;
        }

        $limit = $this->apiLimiter->create($request->getClientIp())->consume();
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(max(1, $limit->getRetryAfter()->getTimestamp() - time()), 'Limite d\'appels atteinte.');
        }

        if (\strlen($request->getContent()) > self::MAX_BODY_SIZE) {
            throw new HttpException(413, 'Le body de la requête dépasse 64 Ko.');
        }
    }

    #[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
    public function onException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->isApiRequest($request)) {
            return;
        }

        if ($event->getThrowable() instanceof NotFoundHttpException) {
            $event->setResponse(new JsonResponse(new \stdClass(), 404));

            return;
        }

        $request->attributes->set('_format', 'json');
    }

    private function isApiRequest(Request $request): bool
    {
        return 1 === preg_match(self::API_PATH, $request->getPathInfo());
    }
}
