<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/card', name: 'api_card_')]
#[OA\Tag(name: 'Card', description: 'Routes for all about cards')]
class ApiCardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/all', name: 'List all cards', methods: ['GET'])]
    #[OA\Get(description: 'Return all cards in the database with pagination')]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'code', in: 'query', required: false, description: 'Code of card', schema: new OA\Schema(type: 'string', default: ""))]
    #[OA\Response(response: 200, description: 'List all cards')]
    public function cardAll(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $code = $request->query->get('code', '');
        $limit = 100;
        $offset = ($page - 1) * $limit;

        $this->logger->info('Fetching cards', ['page' => $page, 'limit' => $limit]);

        $queryBuilder = $this->entityManager->getRepository(Card::class)->createQueryBuilder('c');

        if (!empty($code)) {
            $queryBuilder->where('c.setCode = :code')
                ->setParameter('code', $code);
        }

        $cards = $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->json($cards);
    }

    #[Route('/codes', name: 'List all codes', methods: ['GET'])]
    #[OA\Get(description: 'Get all codes of cards')]
    #[OA\Response(response: 200, description: 'List all codes')]
    public function getCodes(Request $request): Response
    {

        $this->logger->info('Fetching codes');

        $setCodes = $this->entityManager->getRepository(Card::class)
            ->createQueryBuilder('c')
            ->select('DISTINCT c.setCode')
            ->getQuery()
            ->getSingleColumnResult();

        return $this->json($setCodes);
    }

    #[Route('/search', name: 'Search cards', methods: ['GET'])]
    #[OA\Get(description: 'Search cards by name')]
    #[OA\Parameter(
        name: 'q',
        description: 'Search query (min 3 characters)',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', minLength: 3)
    )]
    #[OA\Response(response: 200, description: 'List of matching cards')]
    #[OA\Response(response: 400, description: 'Invalid query (less than 3 characters)')]
    public function cardSearch(Request $request): Response
    {
        $query = $request->query->get('q', '');

        $this->logger->info('Searching cards', ['query' => $query]);

        if (strlen($query) < 3) {
            return $this->json(['error' => 'Search query must be at least 3 characters'], 400);
        }

        $cards = $this->entityManager->getRepository(Card::class)->createQueryBuilder('c')
            ->where('c.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->json($cards);
    }

    #[Route('/{uuid}', name: 'Show card', methods: ['GET'])]
    #[OA\Parameter(name: 'uuid', description: 'UUID of the card', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Put(description: 'Get a card by UUID')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function cardShow(string $uuid): Response
    {

        $this->logger->info('Fetching card by UUID', ['uuid' => $uuid]);

        $card = $this->entityManager->getRepository(Card::class)->findOneBy(['uuid' => $uuid]);
        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }
        return $this->json($card);
    }
}
