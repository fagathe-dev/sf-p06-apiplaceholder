<?php

namespace App\Sandbox;

use App\Entity\Album;
use App\Entity\Comment;
use App\Entity\Photo;
use App\Entity\Post;
use App\Entity\Todo;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('sandbox_purge')]
final readonly class SandboxPurger
{
    // Des enfants vers les parents, pour respecter les clés étrangères
    private const array PURGE_ORDER = [Comment::class, Photo::class, Post::class, Album::class, Todo::class, User::class];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return array<string, int> nombre d'entrées supprimées par ressource
     */
    public function purge(?int $olderThanHours = null): array
    {
        $deleted = [];

        foreach (self::PURGE_ORDER as $class) {
            $query = $this->entityManager->createQueryBuilder()
                ->delete($class, 'resource')
                ->where('resource.isSeed = false');

            if (null !== $olderThanHours) {
                $query->andWhere('resource.createdAt < :limit')
                    ->setParameter('limit', new \DateTimeImmutable(\sprintf('-%d hours', $olderThanHours)));
            }

            $deleted[(new \ReflectionClass($class))->getShortName()] = (int) $query->getQuery()->execute();
        }

        $this->logger->info('Purge de la sandbox : {total} entrée(s) supprimée(s).', [
            'total' => array_sum($deleted),
            'deleted' => $deleted,
            'older_than_hours' => $olderThanHours,
        ]);

        return $deleted;
    }
}
