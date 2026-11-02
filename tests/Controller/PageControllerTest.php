<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PHPUnit\Framework\Attributes\DataProvider;

final class PageControllerTest extends ApiTestCase
{
    #[DataProvider('pages')]
    public function testPageLoadsTheUiKitFromTheDesignSystem(string $uri, string $heading): void
    {
        $crawler = $this->client->request('GET', $uri);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $heading);

        $host = $_ENV['DESIGN_SYSTEM_URL'];
        $this->assertSame([$host.'/assets/ds-icons.css', $host.'/css/ds-ui-kit.css'], \array_slice($crawler->filter('link[rel=stylesheet]')->each(static fn ($link) => $link->attr('href')), 0, 2));
        $this->assertCount(1, $crawler->filter(\sprintf('script[src="%s/js/main.js"]', $host)));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function pages(): iterable
    {
        yield 'accueil' => ['/', 'vos données existent vraiment'];
        yield 'documentation' => ['/docs', 'Documentation de l\'API'];
    }

    public function testHomeOffersTheConsoleAndLinksEveryResource(): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertStringContainsString("const url = 'http://localhost/api/posts';", $crawler->filter('#code-hero')->text());
        $this->assertSame('/api/posts/1', $crawler->filter('#try-url')->attr('value'));
        $this->assertCount(7, $crawler->filter('[data-try-preset]'));
        $this->assertCount(6, $crawler->filter('a.card[href^="/docs/"]'));
    }

    public function testNavbarOnlyLinksTheDocumentation(): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertSame(['Documentation'], $crawler->filter('.navbar-nav .nav-link')->each(static fn ($link) => $link->text()));
    }

    public function testGuideIsPermanentlyRedirectedToTheDocumentation(): void
    {
        $this->client->request('GET', '/guide');

        $this->assertResponseRedirects('/docs', 301);
    }

    public function testHealthCheckReportsADatabaseUp(): void
    {
        $json = $this->request('GET', '/health-check');

        $this->assertResponseIsSuccessful();
        $this->assertSame('ok', $json['status']);
        $this->assertSame(['database' => 'up'], $json['checks']);
        $this->assertNotFalse(\DateTimeImmutable::createFromFormat(\DATE_ATOM, $json['timestamp']));
    }

    public function testHealthCheckReportsADatabaseDown(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($this->createStub(MySQLPlatform::class));
        $connection->method('executeQuery')->willThrowException(new class('Serveur injoignable') extends \RuntimeException implements DbalException {});
        static::getContainer()->set(Connection::class, $connection);
        $this->client->disableReboot();

        $json = $this->request('GET', '/health-check');

        $this->assertResponseStatusCodeSame(503);
        $this->assertSame('error', $json['status']);
        $this->assertSame(['database' => 'down'], $json['checks']);
    }
}
