<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/articles', name: 'api_article_')]
final class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleRepository $repository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {}

    // GET /api/articles
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $articles = $this->repository->findAll();
        return $this->json($articles, Response::HTTP_OK, [], ['groups' => 'article:read']);
    }

    // GET /api/articles/{id}
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Article $article): JsonResponse
    {
        return $this->json($article, Response::HTTP_OK, [], ['groups' => 'article:read']);
    }

    // POST /api/articles
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $article = $this->serializer->deserialize(
            $request->getContent(),
            Article::class,
            'json',
            ['groups' => 'article:write']
        );

        $article->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($article);
        $this->em->flush();

        return $this->json($article, Response::HTTP_CREATED, [], ['groups' => 'article:read']);
    }

    // PUT /api/articles/{id}
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Article $article, Request $request): JsonResponse
    {
        $this->serializer->deserialize(
            $request->getContent(),
            Article::class,
            'json',
            ['groups' => 'article:write', 'object_to_populate' => $article]
        );

        $this->em->flush();

        return $this->json($article, Response::HTTP_OK, [], ['groups' => 'article:read']);
    }

    // DELETE /api/articles/{id}
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Article $article): JsonResponse
    {
        $this->em->remove($article);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
