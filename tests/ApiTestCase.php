<?php

namespace App\Tests;

use App\Dto\AlbumInput;
use App\Dto\CommentInput;
use App\Dto\PostInput;
use App\Dto\UserInput;
use App\Entity\Album;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        static::getContainer()->get('cache.rate_limiter_test')->clear();
    }

    protected function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * @param array<string, mixed>|null $payload
     *
     * @return array<mixed>
     */
    protected function request(string $method, string $uri, ?array $payload = null): array
    {
        $this->client->jsonRequest($method, $uri, $payload ?? []);

        return json_decode($this->client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR);
    }

    protected function createUser(bool $seed = false, string $name = 'Leanne'): User
    {
        $user = new User();
        $user->update(new UserInput(name: $name), false);

        return $this->store($user, $seed);
    }

    protected function createPost(User $user, bool $seed = false, string $title = 'Titre'): Post
    {
        $post = new Post();
        $post->update(new PostInput(title: $title, body: 'Contenu'), $user);

        return $this->store($post, $seed);
    }

    protected function createComment(Post $post, bool $seed = false): Comment
    {
        $comment = new Comment();
        $comment->update(new CommentInput(name: 'Nom', body: 'Contenu'), $post, false);

        return $this->store($comment, $seed);
    }

    protected function createAlbum(User $user, bool $seed = false): Album
    {
        $album = new Album();
        $album->update(new AlbumInput(title: 'Album'), $user);

        return $this->store($album, $seed);
    }

    /**
     * @template T of object
     *
     * @param T $resource
     *
     * @return T
     */
    private function store(object $resource, bool $seed): object
    {
        if ($seed) {
            $resource->markAsSeed();
        }

        $this->entityManager()->persist($resource);
        $this->entityManager()->flush();

        return $resource;
    }
}
