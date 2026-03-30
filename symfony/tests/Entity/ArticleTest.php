<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testArticleCreation(): void
    {
        $article = new Article();
        $article->setTitle('Mon premier article');
        $article->setContent('Contenu de test');
        $article->setCreatedAt(new \DateTimeImmutable());

        $this->assertSame('Mon premier article', $article->getTitle());
        $this->assertSame('Contenu de test', $article->getContent());
        $this->assertInstanceOf(\DateTimeImmutable::class, $article->getCreatedAt());
    }

    public function testTitleCannotBeEmpty(): void
    {
        $article = new Article();
        $article->setTitle('');

        $this->assertSame('', $article->getTitle());
        $this->assertNotNull($article->getTitle());
    }

    public function testArticleIdIsNullBeforePersist(): void
    {
        $article = new Article();

        $this->assertNull($article->getId());
    }
}
