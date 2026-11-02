<?php

namespace App\Documentation;

/**
 * Description des ressources affichée par la documentation de l'API.
 */
final class ApiCatalog
{
    private const RESOURCES = [
        'posts' => [
            'slug' => 'posts',
            'label' => 'Posts',
            'icon' => 'ri-article-line',
            'count' => 100,
            'description' => 'Articles de blog liés à un utilisateur.',
            'parent' => 'userId',
            'fields' => [
                ['name' => 'id', 'type' => 'integer', 'required' => false, 'rule' => 'Généré par l\'API'],
                ['name' => 'userId', 'type' => 'integer', 'required' => true, 'rule' => 'Utilisateur existant, sinon 400'],
                ['name' => 'title', 'type' => 'string', 'required' => true, 'rule' => '255 caractères au maximum'],
                ['name' => 'body', 'type' => 'string', 'required' => true, 'rule' => '5000 caractères au maximum'],
            ],
            'filters' => ['userId', 'title'],
            'nested' => [['uri' => 'comments', 'description' => 'Commentaires du post']],
            'payload' => ['title' => 'Mon nouveau post', 'body' => 'Contenu enregistré en base de données', 'userId' => 1],
            'patch' => ['title' => 'Titre modifié'],
        ],
        'comments' => [
            'slug' => 'comments',
            'label' => 'Comments',
            'icon' => 'ri-chat-3-line',
            'count' => 500,
            'description' => 'Commentaires liés à un post.',
            'parent' => 'postId',
            'fields' => [
                ['name' => 'id', 'type' => 'integer', 'required' => false, 'rule' => 'Généré par l\'API'],
                ['name' => 'postId', 'type' => 'integer', 'required' => true, 'rule' => 'Post existant, sinon 400'],
                ['name' => 'name', 'type' => 'string', 'required' => true, 'rule' => '255 caractères au maximum'],
                ['name' => 'email', 'type' => 'string', 'required' => false, 'rule' => 'Format email valide'],
                ['name' => 'body', 'type' => 'string', 'required' => true, 'rule' => '5000 caractères au maximum'],
            ],
            'filters' => ['postId', 'name', 'email'],
            'nested' => [],
            'payload' => ['postId' => 1, 'name' => 'Mon commentaire', 'email' => 'jane@exemple.com', 'body' => 'Merci pour cet article'],
            'patch' => ['body' => 'Commentaire modifié'],
        ],
        'albums' => [
            'slug' => 'albums',
            'label' => 'Albums',
            'icon' => 'ri-folder-image-line',
            'count' => 100,
            'description' => 'Albums photos liés à un utilisateur.',
            'parent' => 'userId',
            'fields' => [
                ['name' => 'id', 'type' => 'integer', 'required' => false, 'rule' => 'Généré par l\'API'],
                ['name' => 'userId', 'type' => 'integer', 'required' => true, 'rule' => 'Utilisateur existant, sinon 400'],
                ['name' => 'title', 'type' => 'string', 'required' => true, 'rule' => '255 caractères au maximum'],
            ],
            'filters' => ['userId', 'title'],
            'nested' => [['uri' => 'photos', 'description' => 'Photos de l\'album']],
            'payload' => ['userId' => 1, 'title' => 'Mon album'],
            'patch' => ['title' => 'Album renommé'],
        ],
        'photos' => [
            'slug' => 'photos',
            'label' => 'Photos',
            'icon' => 'ri-image-line',
            'count' => 5000,
            'description' => 'Photos liées à un album.',
            'parent' => 'albumId',
            'fields' => [
                ['name' => 'id', 'type' => 'integer', 'required' => false, 'rule' => 'Généré par l\'API'],
                ['name' => 'albumId', 'type' => 'integer', 'required' => true, 'rule' => 'Album existant, sinon 400'],
                ['name' => 'title', 'type' => 'string', 'required' => true, 'rule' => '255 caractères au maximum'],
                ['name' => 'url', 'type' => 'string', 'required' => false, 'rule' => 'URL valide, 500 caractères au maximum'],
                ['name' => 'thumbnailUrl', 'type' => 'string', 'required' => false, 'rule' => 'URL valide, 500 caractères au maximum'],
            ],
            'filters' => ['albumId', 'title'],
            'nested' => [],
            'payload' => ['albumId' => 1, 'title' => 'Ma photo', 'url' => 'https://exemple.com/600.png', 'thumbnailUrl' => 'https://exemple.com/150.png'],
            'patch' => ['title' => 'Photo renommée'],
        ],
        'todos' => [
            'slug' => 'todos',
            'label' => 'Todos',
            'icon' => 'ri-checkbox-circle-line',
            'count' => 200,
            'description' => 'Tâches, à faire ou terminées, liées à un utilisateur.',
            'parent' => 'userId',
            'fields' => [
                ['name' => 'id', 'type' => 'integer', 'required' => false, 'rule' => 'Généré par l\'API'],
                ['name' => 'userId', 'type' => 'integer', 'required' => true, 'rule' => 'Utilisateur existant, sinon 400'],
                ['name' => 'title', 'type' => 'string', 'required' => true, 'rule' => '255 caractères au maximum'],
                ['name' => 'completed', 'type' => 'boolean', 'required' => false, 'rule' => 'false par défaut'],
            ],
            'filters' => ['userId', 'title', 'completed'],
            'nested' => [],
            'payload' => ['userId' => 1, 'title' => 'Ma tâche', 'completed' => false],
            'patch' => ['completed' => true],
        ],
        'users' => [
            'slug' => 'users',
            'label' => 'Users',
            'icon' => 'ri-user-line',
            'count' => 10,
            'description' => 'Comptes utilisateurs avec adresse et entreprise.',
            'parent' => null,
            'fields' => [
                ['name' => 'id', 'type' => 'integer', 'required' => false, 'rule' => 'Généré par l\'API'],
                ['name' => 'name', 'type' => 'string', 'required' => true, 'rule' => '255 caractères au maximum'],
                ['name' => 'username', 'type' => 'string', 'required' => false, 'rule' => '255 caractères au maximum'],
                ['name' => 'email', 'type' => 'string', 'required' => false, 'rule' => 'Format email valide'],
                ['name' => 'address', 'type' => 'object', 'required' => false, 'rule' => 'street, suite, city, zipcode, geo { lat, lng }'],
                ['name' => 'phone', 'type' => 'string', 'required' => false, 'rule' => '255 caractères au maximum'],
                ['name' => 'website', 'type' => 'string', 'required' => false, 'rule' => '255 caractères au maximum'],
                ['name' => 'company', 'type' => 'object', 'required' => false, 'rule' => 'name, catchPhrase, bs'],
            ],
            'filters' => ['name', 'username', 'email'],
            'nested' => [
                ['uri' => 'albums', 'description' => 'Albums de l\'utilisateur'],
                ['uri' => 'todos', 'description' => 'Todos de l\'utilisateur'],
                ['uri' => 'posts', 'description' => 'Posts de l\'utilisateur'],
            ],
            'payload' => [
                'name' => 'Jane Doe',
                'username' => 'jane',
                'email' => 'jane@exemple.com',
                'address' => [
                    'street' => '1 rue de la Paix',
                    'suite' => 'Apt. 12',
                    'city' => 'Paris',
                    'zipcode' => '75002',
                    'geo' => ['lat' => '48.8686', 'lng' => '2.3314'],
                ],
                'phone' => '01 23 45 67 89',
                'website' => 'exemple.com',
                'company' => ['name' => 'Exemple', 'catchPhrase' => 'Une accroche', 'bs' => 'Une activité'],
            ],
            'patch' => ['phone' => '09 87 65 43 21'],
        ],
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return self::RESOURCES;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        return self::RESOURCES[$slug] ?? null;
    }

    /**
     * Exemples proposés par la console de requêtes, toutes ressources confondues.
     *
     * @return list<array{method: string, url: string, body: string}>
     */
    public function presets(): array
    {
        return [
            $this->preset('GET', '/api/posts/1'),
            $this->preset('GET', '/api/posts?userId=1&_limit=3'),
            $this->preset('GET', '/api/users/1'),
            $this->preset('GET', '/api/users/1/todos?_limit=3'),
            $this->preset('POST', '/api/posts', self::RESOURCES['posts']['payload']),
            $this->preset('PATCH', '/api/posts/1', self::RESOURCES['posts']['patch']),
            $this->preset('DELETE', '/api/posts/1'),
        ];
    }

    /**
     * Exemples proposés par la console de requêtes sur la page d'une ressource.
     *
     * @param array<string, mixed> $resource
     *
     * @return list<array{method: string, url: string, body: string}>
     */
    public function resourcePresets(array $resource): array
    {
        $uri = '/api/'.$resource['slug'];
        $presets = [$this->preset('GET', $uri.'?_limit=3'), $this->preset('GET', $uri.'/1')];

        if (null !== $resource['parent']) {
            $presets[] = $this->preset('GET', \sprintf('%s?%s=1&_limit=3', $uri, $resource['parent']));
        }
        foreach ($resource['nested'] as $nested) {
            $presets[] = $this->preset('GET', \sprintf('%s/1/%s?_limit=3', $uri, $nested['uri']));
        }

        return [
            ...$presets,
            $this->preset('POST', $uri, $resource['payload']),
            $this->preset('PUT', $uri.'/1', $resource['payload']),
            $this->preset('PATCH', $uri.'/1', $resource['patch']),
            $this->preset('DELETE', $uri.'/1'),
        ];
    }

    /**
     * @param array<string, mixed>|null $body
     *
     * @return array{method: string, url: string, body: string}
     */
    private function preset(string $method, string $url, ?array $body = null): array
    {
        return [
            'method' => $method,
            'url' => $url,
            'body' => null === $body ? '' : json_encode($body, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR),
        ];
    }
}
