<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Importe les données d'origine de JSONPlaceholder en conservant leurs identifiants (DBAL plutôt
 * que l'ORM : les ids sont imposés et 5 000 photos passent bien plus vite).
 */
class AppFixtures extends Fixture
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/fixtures/data')]
        private readonly string $dataDir,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        \assert($manager instanceof EntityManagerInterface);
        $connection = $manager->getConnection();

        $connection->transactional(function (Connection $connection): void {
            $this->import($connection, 'users', static fn (array $user): array => [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'website' => $user['website'],
                'address_street' => $user['address']['street'],
                'address_suite' => $user['address']['suite'],
                'address_city' => $user['address']['city'],
                'address_zipcode' => $user['address']['zipcode'],
                'address_geo_lat' => $user['address']['geo']['lat'],
                'address_geo_lng' => $user['address']['geo']['lng'],
                'company_name' => $user['company']['name'],
                'company_catch_phrase' => $user['company']['catchPhrase'],
                'company_bs' => $user['company']['bs'],
            ]);
            $this->import($connection, 'posts', static fn (array $post): array => [
                'id' => $post['id'],
                'user_id' => $post['userId'],
                'title' => $post['title'],
                'body' => $post['body'],
            ]);
            $this->import($connection, 'comments', static fn (array $comment): array => [
                'id' => $comment['id'],
                'post_id' => $comment['postId'],
                'name' => $comment['name'],
                'email' => $comment['email'],
                'body' => $comment['body'],
            ]);
            $this->import($connection, 'albums', static fn (array $album): array => [
                'id' => $album['id'],
                'user_id' => $album['userId'],
                'title' => $album['title'],
            ]);
            $this->import($connection, 'photos', static fn (array $photo): array => [
                'id' => $photo['id'],
                'album_id' => $photo['albumId'],
                'title' => $photo['title'],
                'url' => $photo['url'],
                'thumbnail_url' => $photo['thumbnailUrl'],
            ]);
            $this->import($connection, 'todos', static fn (array $todo): array => [
                'id' => $todo['id'],
                'user_id' => $todo['userId'],
                'title' => $todo['title'],
                'completed' => $todo['completed'],
            ], ['completed' => Types::BOOLEAN]);
        });
    }

    /**
     * @param callable(array<string, mixed>): array<string, mixed> $toRow
     * @param array<string, string>                                $types
     */
    private function import(Connection $connection, string $table, callable $toRow, array $types = []): void
    {
        $createdAt = new \DateTimeImmutable();
        $items = json_decode(file_get_contents(\sprintf('%s/%s.json', $this->dataDir, $table)), true, flags: \JSON_THROW_ON_ERROR);

        foreach ($items as $item) {
            $connection->insert(
                $table,
                $toRow($item) + ['is_seed' => true, 'created_at' => $createdAt],
                $types + ['is_seed' => Types::BOOLEAN, 'created_at' => Types::DATETIME_IMMUTABLE],
            );
        }

        // PostgreSQL n'avance pas sa séquence sur un id imposé, contrairement à MySQL et MariaDB
        if ($connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $connection->executeStatement(\sprintf("SELECT setval(pg_get_serial_sequence('%1\$s', 'id'), MAX(id)) FROM %1\$s", $table));
        }
    }
}
