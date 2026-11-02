<?php

namespace App\Sandbox;

use App\Dto\PageQuery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Lectures et écritures des ressources : une donnée de seed n'est jamais modifiée en base,
 * l'écriture est simplement ignorée et la réponse reflète l'état simulé.
 */
final readonly class ResourceManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @template T of object
     *
     * @param class-string<T>      $class
     * @param array<string, mixed> $criteria
     *
     * @return list<T>
     */
    public function list(string $class, array $criteria, PageQuery $page): array
    {
        return array_values($this->entityManager->getRepository($class)->findBy($criteria, ['id' => 'ASC'], $page->limit, $page->start));
    }

    /**
     * Traduit la query string en critères de recherche, ou null si une valeur ne peut correspondre à rien.
     *
     * @param class-string          $class
     * @param array<string, string> $filters  nom du paramètre => champ de l'entité
     * @param array<string, mixed>  $query
     *
     * @return array<string, mixed>|null
     */
    public function criteria(string $class, array $filters, array $query): ?array
    {
        $metadata = $this->entityManager->getClassMetadata($class);
        $criteria = [];

        foreach (array_intersect_key($filters, $query) as $parameter => $field) {
            $value = $query[$parameter];
            if (!\is_string($value)) {
                return null;
            }

            $value = match (true) {
                $metadata->hasAssociation($field) => ctype_digit($value) ? (int) $value : null,
                'boolean' === $metadata->getTypeOfField($field) => filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE),
                default => $value,
            };
            if (null === $value) {
                return null;
            }

            $criteria[$field] = $value;
        }

        return $criteria;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function get(string $class, int $id): object
    {
        return $this->entityManager->find($class, $id) ?? throw new NotFoundHttpException();
    }

    /**
     * Résout une clé étrangère : null si elle n'est pas fournie, 400 si elle n'existe pas.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    public function reference(string $class, ?int $id): ?object
    {
        if (null === $id) {
            return null;
        }

        return $this->entityManager->find($class, $id) ?? throw new BadRequestHttpException(\sprintf('La ressource liée %s n°%d n\'existe pas.', (new \ReflectionClass($class))->getShortName(), $id));
    }

    public function save(object $resource): void
    {
        if ($resource->isSeed) {
            return;
        }

        $this->entityManager->persist($resource);
        $this->entityManager->flush();
    }
}
