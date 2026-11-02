<?php

namespace App\Tests\Controller;

use App\Documentation\ApiCatalog;
use App\Tests\ApiTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class DocumentationControllerTest extends ApiTestCase
{
    public function testIndexLinksEveryResource(): void
    {
        $crawler = $this->client->request('GET', '/docs');

        $this->assertResponseIsSuccessful();
        foreach (array_keys(new ApiCatalog()->all()) as $slug) {
            $this->assertCount(2, $crawler->filter(\sprintf('a[href="/docs/%s"]', $slug)), $slug);
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function slugs(): iterable
    {
        foreach (array_keys(new ApiCatalog()->all()) as $slug) {
            yield $slug => [$slug];
        }
    }

    #[DataProvider('slugs')]
    public function testResourcePageDocumentsFieldsAndExamples(string $slug): void
    {
        $resource = new ApiCatalog()->find($slug);

        $crawler = $this->client->request('GET', '/docs/'.$slug);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $resource['label']);
        $this->assertSelectorExists('a.nav-link.active[href="/docs/'.$slug.'"]');
        $this->assertSame(array_column($resource['fields'], 'name'), $crawler->filter('section table tbody tr td:first-child code')->each(static fn ($cell) => $cell->text()));
        $this->assertStringContainsString("fetch('http://localhost/api/{$slug}?_limit=10')", $crawler->filter("#code-{$slug}-list")->text());
        $this->assertStringContainsString('"', $crawler->filter("#code-{$slug}-create")->text());
        $this->assertStringNotContainsString('.json()', $crawler->filter("#code-{$slug}-delete")->text());

        $this->assertCount(1, $crawler->filter('form#try-it'));
        $this->assertSame("/api/{$slug}?_limit=3", $crawler->filter('#try-url')->attr('value'));
        foreach ($crawler->filter('[data-try-preset]')->each(static fn ($preset) => $preset->attr('data-url')) as $url) {
            $this->assertStringStartsWith("/api/{$slug}", $url);
        }
        $this->assertCount(1, $crawler->filter('script[src="/js/pages/try-it.js"]'));
        $this->assertStringNotContainsString('core-ts', $this->client->getResponse()->getContent());
    }

    /**
     * Ce que la documentation annonce doit correspondre à ce que l'API accepte et renvoie.
     */
    #[DataProvider('slugs')]
    public function testDocumentedPayloadIsAcceptedAndMatchesTheFields(string $slug): void
    {
        $resource = new ApiCatalog()->find($slug);
        $payload = $resource['payload'];

        $user = $this->createUser();
        $parents = [
            'userId' => $user->id,
            'postId' => $this->createPost($user)->id,
            'albumId' => $this->createAlbum($user)->id,
        ];
        if (null !== $resource['parent']) {
            $payload[$resource['parent']] = $parents[$resource['parent']];
        }

        $created = $this->request('POST', '/api/'.$slug, $payload);
        $this->assertResponseStatusCodeSame(201);

        $expected = array_column($resource['fields'], 'name');
        $this->assertEqualsCanonicalizing($expected, array_keys($created));

        $this->request('PATCH', \sprintf('/api/%s/%d', $slug, $created['id']), $resource['patch']);
        $this->assertResponseIsSuccessful();
    }

    public function testUnknownResourceIsNotFound(): void
    {
        foreach (['/docs/inconnu', '/docs/core-ts'] as $uri) {
            $this->client->request('GET', $uri);

            $this->assertResponseStatusCodeSame(404);
        }
    }

    #[DataProvider('layoutPages')]
    public function testLayoutShowsLogoAndFavicon(string $uri): void
    {
        $crawler = $this->client->request('GET', $uri);

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter(\sprintf('link[rel="shortcut icon"][href="/%s"]', $_ENV['APP_FAVICON'])));
        $this->assertCount(2, $crawler->filter(\sprintf('img[src="/%s"]', $_ENV['APP_LOGO'])));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function layoutPages(): iterable
    {
        yield 'accueil' => ['/'];
        yield 'documentation' => ['/docs'];
    }
}
