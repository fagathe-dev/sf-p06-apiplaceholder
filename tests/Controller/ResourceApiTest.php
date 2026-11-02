<?php

namespace App\Tests\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Tests\ApiTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ResourceApiTest extends ApiTestCase
{
    public function testListSerializesTheDocumentedFieldsOnly(): void
    {
        $user = $this->createUser();
        $post = $this->createPost($user, seed: true);

        $this->assertSame([
            ['userId' => $user->id, 'id' => $post->id, 'title' => 'Titre', 'body' => 'Contenu'],
        ], $this->request('GET', '/api/posts?userId='.$user->id));
        $this->assertResponseIsSuccessful();
    }

    public function testFiltersAndPagination(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        $first = $this->createPost($user);
        $second = $this->createPost($user);
        $third = $this->createPost($user);
        $this->createPost($other);

        $ids = array_column($this->request('GET', \sprintf('/api/posts?userId=%d&_start=1&_limit=1', $user->id)), 'id');
        $this->assertSame([$second->id], $ids);

        $ids = array_column($this->request('GET', '/api/posts?userId='.$user->id), 'id');
        $this->assertSame([$first->id, $second->id, $third->id], $ids);

        $this->assertSame([], $this->request('GET', '/api/posts?userId=abc'));
    }

    public function testBooleanFilter(): void
    {
        $user = $this->createUser();
        $this->request('POST', '/api/todos', ['userId' => $user->id, 'title' => 'Fait', 'completed' => true]);
        $this->request('POST', '/api/todos', ['userId' => $user->id, 'title' => 'À faire']);

        $todos = $this->request('GET', \sprintf('/api/todos?userId=%d&completed=true', $user->id));
        $this->assertSame(['Fait'], array_column($todos, 'title'));

        $todos = $this->request('GET', \sprintf('/api/todos?userId=%d&completed=false', $user->id));
        $this->assertSame(['À faire'], array_column($todos, 'title'));
    }

    public function testInvalidPaginationIsRejected(): void
    {
        $this->request('GET', '/api/posts?_limit=-1');
        $this->assertResponseStatusCodeSame(422);
    }

    public function testMissingResourceReturnsEmptyObject(): void
    {
        foreach (['/api/posts/999999999', '/api/posts/abc'] as $uri) {
            $this->client->jsonRequest('GET', $uri);
            $this->assertResponseStatusCodeSame(404);
            $this->assertSame('{}', $this->client->getResponse()->getContent());
        }
    }

    public function testCreatePersistsAUserResource(): void
    {
        $user = $this->createUser();

        $json = $this->request('POST', '/api/posts', ['userId' => $user->id, 'title' => 'Nouveau', 'body' => 'Corps', 'isSeed' => true]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('Nouveau', $json['title']);
        $this->assertArrayNotHasKey('isSeed', $json);

        $this->entityManager()->clear();
        $post = $this->entityManager()->find(Post::class, $json['id']);
        $this->assertNotNull($post);
        $this->assertFalse($post->isSeed);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('invalidPayloads')]
    public function testValidation(string $uri, array $payload, string $field): void
    {
        $json = $this->request('POST', $uri, $payload);

        $this->assertResponseStatusCodeSame(422);
        $this->assertContains($field, array_column($json['violations'], 'propertyPath'));
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>, string}>
     */
    public static function invalidPayloads(): iterable
    {
        yield 'titre manquant' => ['/api/posts', ['userId' => 1, 'body' => 'Corps'], 'title'];
        yield 'titre vide' => ['/api/posts', ['userId' => 1, 'title' => '   ', 'body' => 'Corps'], 'title'];
        yield 'titre trop long' => ['/api/posts', ['userId' => 1, 'title' => str_repeat('a', 256), 'body' => 'Corps'], 'title'];
        yield 'body trop long' => ['/api/posts', ['userId' => 1, 'title' => 'Titre', 'body' => str_repeat('a', 5001)], 'body'];
        yield 'email invalide' => ['/api/comments', ['postId' => 1, 'name' => 'Nom', 'body' => 'Corps', 'email' => 'invalide'], 'email'];
        yield 'url invalide' => ['/api/photos', ['albumId' => 1, 'title' => 'Titre', 'url' => 'invalide'], 'url'];
        yield 'url trop longue' => ['/api/photos', ['albumId' => 1, 'title' => 'Titre', 'thumbnailUrl' => 'https://exemple.com/'.str_repeat('a', 500)], 'thumbnailUrl'];
        yield 'nom utilisateur manquant' => ['/api/users', ['username' => 'Bret'], 'name'];
    }

    public function testUnknownForeignKeyIsABadRequest(): void
    {
        $this->request('POST', '/api/posts', ['userId' => 999999999, 'title' => 'Titre', 'body' => 'Corps']);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testMalformedJsonIsABadRequest(): void
    {
        $this->client->request('POST', '/api/posts', server: ['CONTENT_TYPE' => 'application/json'], content: '{oups');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testBodyLargerThan64KiloBytesIsRejected(): void
    {
        $user = $this->createUser();

        $this->request('POST', '/api/posts', ['userId' => $user->id, 'title' => 'Titre', 'body' => str_repeat('a', 65 * 1024)]);

        $this->assertResponseStatusCodeSame(413);
    }

    public function testMethodNotAllowedIsRenderedAsJson(): void
    {
        $this->client->request('POST', '/api/posts/1');

        $this->assertResponseStatusCodeSame(405);
        $this->assertSame(405, json_decode($this->client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR)['status']);
    }

    public function testWritesOnASeedAreSimulated(): void
    {
        $user = $this->createUser(seed: true);
        $post = $this->createPost($user, seed: true);
        $uri = '/api/posts/'.$post->id;
        $id = $post->id;

        $json = $this->request('PUT', $uri, ['userId' => $user->id, 'title' => 'Remplacé', 'body' => 'Corps']);
        $this->assertResponseIsSuccessful();
        $this->assertSame('Remplacé', $json['title']);

        $json = $this->request('PATCH', $uri, ['title' => 'Modifié']);
        $this->assertResponseIsSuccessful();
        $this->assertSame('Modifié', $json['title']);
        $this->assertSame('Contenu', $json['body']);

        $this->entityManager()->clear();
        $this->assertSame('Titre', $this->entityManager()->find(Post::class, $id)->title);
    }

    public function testWritesOnAUserResourceArePersisted(): void
    {
        $user = $this->createUser();
        $post = $this->createPost($user);
        $id = $post->id;
        $uri = '/api/posts/'.$id;

        $this->request('PUT', $uri, ['id' => $id, 'userId' => $user->id, 'title' => 'Remplacé', 'body' => 'Corps']);
        $this->assertResponseIsSuccessful();
        $this->entityManager()->clear();
        $this->assertSame('Remplacé', $this->entityManager()->find(Post::class, $id)->title);

        $this->request('PATCH', $uri, ['body' => 'Modifié']);
        $this->assertResponseIsSuccessful();
        $this->entityManager()->clear();
        $stored = $this->entityManager()->find(Post::class, $id);
        $this->assertSame('Remplacé', $stored->title);
        $this->assertSame('Modifié', $stored->body);
    }

    public function testPutRequiresEveryMandatoryField(): void
    {
        $post = $this->createPost($this->createUser());

        $this->request('PUT', '/api/posts/'.$post->id, ['title' => 'Sans le reste']);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testDeleteAnswersNoContentWithoutDeletingAnything(): void
    {
        $user = $this->createUser();
        $userPost = $this->createPost($user);
        $seedPost = $this->createPost($user, seed: true);
        $comment = $this->createComment($userPost);
        $ids = [$userPost->id, $seedPost->id];
        $commentId = $comment->id;

        foreach ([...$ids, 999999999] as $id) {
            $this->client->jsonRequest('DELETE', '/api/posts/'.$id);

            $this->assertResponseStatusCodeSame(204);
            $this->assertSame('', $this->client->getResponse()->getContent());
        }

        $this->entityManager()->clear();
        $this->assertNotNull($this->entityManager()->find(Post::class, $ids[0]));
        $this->assertNotNull($this->entityManager()->find(Post::class, $ids[1]));
        $this->assertNotNull($this->entityManager()->find(Comment::class, $commentId));
    }

    public function testNestedRoutes(): void
    {
        $user = $this->createUser();
        $post = $this->createPost($user);
        $comment = $this->createComment($post);
        $album = $this->createAlbum($user);
        $photo = $this->request('POST', '/api/photos', ['albumId' => $album->id, 'title' => 'Photo', 'url' => 'https://exemple.com/1.png', 'thumbnailUrl' => 'https://exemple.com/2.png']);
        $todo = $this->request('POST', '/api/todos', ['userId' => $user->id, 'title' => 'Tâche']);

        $this->assertSame([$comment->id], array_column($this->request('GET', "/api/posts/{$post->id}/comments"), 'id'));
        $this->assertSame([$photo['id']], array_column($this->request('GET', "/api/albums/{$album->id}/photos"), 'id'));
        $this->assertSame([$album->id], array_column($this->request('GET', "/api/users/{$user->id}/albums"), 'id'));
        $this->assertSame([$todo['id']], array_column($this->request('GET', "/api/users/{$user->id}/todos"), 'id'));
        $this->assertSame([$post->id], array_column($this->request('GET', "/api/users/{$user->id}/posts"), 'id'));
        $this->assertSame([$comment->id], array_column($this->request('GET', "/api/comments?postId={$post->id}"), 'id'));
    }

    public function testUserKeepsItsNestedStructure(): void
    {
        $payload = [
            'name' => 'Leanne Graham',
            'username' => 'Bret',
            'email' => 'Sincere@april.biz',
            'address' => [
                'street' => 'Kulas Light',
                'suite' => 'Apt. 556',
                'city' => 'Gwenborough',
                'zipcode' => '92998-3874',
                'geo' => ['lat' => '-37.3159', 'lng' => '81.1496'],
            ],
            'phone' => '1-770-736-8031 x56442',
            'website' => 'hildegard.org',
            'company' => ['name' => 'Romaguera-Crona', 'catchPhrase' => 'Multi-layered', 'bs' => 'harness'],
        ];

        $created = $this->request('POST', '/api/users', $payload);
        $this->assertResponseStatusCodeSame(201);

        $this->entityManager()->clear();
        $this->assertSame(['id' => $created['id']] + $payload, $this->request('GET', '/api/users/'.$created['id']));

        $patched = $this->request('PATCH', '/api/users/'.$created['id'], ['address' => ['city' => 'Paris', 'geo' => ['lat' => '48.85']]]);
        $this->assertResponseIsSuccessful();
        $this->assertSame('Paris', $patched['address']['city']);
        $this->assertSame('48.85', $patched['address']['geo']['lat']);
        $this->assertSame('Bret', $patched['username']);

        $this->entityManager()->clear();
        $this->assertSame('Paris', $this->entityManager()->find(User::class, $created['id'])->address->city);
    }

    public function testCorsAllowsEveryOrigin(): void
    {
        $this->client->request('GET', '/api/posts', server: ['HTTP_ORIGIN' => 'https://exemple.com']);

        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
    }

    public function testRateLimitIsAppliedPerIp(): void
    {
        for ($i = 0; $i < 20; ++$i) {
            $this->client->jsonRequest('GET', '/api/posts?_limit=1');
            $this->assertResponseIsSuccessful();
        }

        $this->client->jsonRequest('GET', '/api/posts?_limit=1');
        $this->assertResponseStatusCodeSame(429);
        $this->assertResponseHasHeader('retry-after');

        // Une autre IP garde son propre quota
        $this->client->jsonRequest('GET', '/api/posts?_limit=1', server: ['REMOTE_ADDR' => '10.0.0.9']);
        $this->assertResponseIsSuccessful();
    }
}
