<?php

namespace App\Tests\Command;

use App\Entity\Post;
use App\Entity\User;
use App\Tests\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class SandboxPurgeCommandTest extends ApiTestCase
{
    public function testPurgeRemovesUserDataAndKeepsSeeds(): void
    {
        $seedUser = $this->createUser(seed: true);
        $seedPost = $this->createPost($seedUser, seed: true);
        $seedComment = $this->createComment($seedPost, seed: true);
        $userComment = $this->createComment($seedPost);
        $user = $this->createUser();
        $post = $this->createPost($user);
        $ids = [$seedUser->id, $seedPost->id, $user->id, $post->id, $seedComment->id, $userComment->id];

        $tester = $this->tester();
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('3 entrée(s) supprimée(s)', $tester->getDisplay());

        $this->entityManager()->clear();
        $this->assertNotNull($this->entityManager()->find(User::class, $ids[0]));
        $this->assertNotNull($this->entityManager()->find(Post::class, $ids[1]));
        $this->assertNull($this->entityManager()->find(User::class, $ids[2]));
        $this->assertNull($this->entityManager()->find(Post::class, $ids[3]));
    }

    public function testOlderThanKeepsRecentEntries(): void
    {
        $user = $this->createUser();
        $id = $user->id;

        $this->tester(['--older-than' => '1'])->assertCommandIsSuccessful();
        $this->entityManager()->clear();
        $this->assertNotNull($this->entityManager()->find(User::class, $id));

        $this->tester(['--older-than' => '0'])->assertCommandIsSuccessful();
        $this->entityManager()->clear();
        $this->assertNotNull($this->entityManager()->find(User::class, $id), 'Créé dans la même seconde : pas encore plus vieux que la limite.');

        $this->entityManager()->getConnection()->executeStatement('UPDATE users SET created_at = ? WHERE id = ?', ['2000-01-01 00:00:00', $id]);
        $this->tester(['--older-than' => '1'])->assertCommandIsSuccessful();
        $this->entityManager()->clear();
        $this->assertNull($this->entityManager()->find(User::class, $id));
    }

    public function testNegativeOlderThanIsRefused(): void
    {
        $tester = $this->tester(['--older-than' => '-1']);

        $this->assertSame(2, $tester->getStatusCode());
    }

    /**
     * @param array<string, string> $input
     */
    private function tester(array $input = []): CommandTester
    {
        $tester = new CommandTester(new Application(static::$kernel)->find('app:sandbox:purge'));
        $tester->execute($input);

        return $tester;
    }
}
