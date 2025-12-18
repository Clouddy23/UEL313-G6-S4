<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Link;
use App\Repository\LinkRepository;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

final class LinkController extends AbstractController
{
    #[OA\Get(
        path: '/api/links',
        summary: 'Retourne la liste de tous les liens',
        tags: ['Links'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retourne la liste de tous les liens',
            )
        ]
    )]
    #[Route('/api/links', name: 'api_link_list', methods: ['GET'])]
    public function apiListLinks(LinkRepository $linkRepository): Response
    {
        // Using repository method to get links with tags and users
        $links = $linkRepository->findAllWithTagsAndUsers();

        $data = [];
        if (!empty($links)) {
            $data = array_map(function (Link $link) {
                return [
                    'id' => $link->getId(),
                    'url' => $link->getUrl(),
                    'title' => $link->getTitle(),
                    'desc' => $link->getDesc(),
                    'user_id' => $link->getUser() ? $link->getUser()->getId() : null,
                    'tags' => array_map(function ($tag) {
                        return [
                            'id' => $tag->getId(),
                            'name' => $tag->getName()
                        ];
                    }, $link->getTags()->toArray())
                ];
            }, $links);
        }

        return $this->json(['links' => $data]);
    }

    //-- WEB PAGE RENDERING --
    #[Route('/links', name: 'link_list', methods: ['GET'])]
    public function listLinks(LinkRepository $linkRepository): Response
    {
        // Using repository method to get links with tags and users
        $links = $linkRepository->findAllWithTagsAndUsers();

        return $this->render('index.html.twig', ['links' => $links]);
    }

    #[OA\Post(
        path: '/api/links',
        summary: 'Ajoute un nouveau lien',
        tags: ['Links'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['url', 'title', 'desc', 'user_id'],
                properties: [
                    new OA\Property(property: 'url', type: 'string'),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'desc', type: 'string'),
                    new OA\Property(property: 'user_id', type: 'integer', description: 'ID de l\'utilisateur propriétaire'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Retourne les informations du lien créé',
            ),
            new OA\Response(
                response: 400,
                description: 'Données invalides'
            ),
            new OA\Response(
                response: 404,
                description: 'Utilisateur non trouvé'
            )
        ]
    )]
    #[Route('/api/links', name: 'api_link_add', methods: ['POST'])]
    public function createLink(EntityManagerInterface $entityManager, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Validation basique
        if (!isset($data['url'], $data['title'], $data['desc'], $data['user_id'])) {
            return $this->json(['error' => 'url, title, desc and user_id are required'], 400);
        }

        // On doit lier le Link à un User existant
        $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $link = new Link();
        $link->setUrl($data['url']);
        $link->setTitle($data['title']);
        $link->setDesc($data['desc']);
        $link->setUser($user);

        $entityManager->persist($link);
        $entityManager->flush();

        return $this->json([
            'id' => $link->getId(),
            'url' => $link->getUrl(),
            'title' => $link->getTitle(),
            'desc' => $link->getDesc(),
            'user_id' => $link->getUser()->getId(),
        ], 201);
    }

    #[OA\Put(
        path: '/api/links/{id}',
        summary: 'Modifie un lien existant',
        tags: ['Links'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'url', type: 'string'),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'desc', type: 'string'),
                    new OA\Property(property: 'user_id', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lien modifié',
            ),
            new OA\Response(
                response: 404,
                description: 'Lien ou utilisateur non trouvé'
            )
        ]
    )]
    #[Route('/api/links/{id}', name: 'api_link_update', methods: ['PUT'])]
    public function updateLink(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $link = $entityManager->getRepository(Link::class)->find($id);
        if (!$link) {
            return $this->json(['error' => 'Link not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['url'])) {
            $link->setUrl($data['url']);
        }
        if (isset($data['title'])) {
            $link->setTitle($data['title']);
        }
        if (isset($data['desc'])) {
            $link->setDesc($data['desc']);
        }
        if (isset($data['user_id'])) {
            $user = $entityManager->getRepository(User::class)->find($data['user_id']);
            if (!$user) {
                return $this->json(['error' => 'User not found'], 404);
            }
            $link->setUser($user);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $link->getId(),
            'url' => $link->getUrl(),
            'title' => $link->getTitle(),
            'desc' => $link->getDesc(),
            'user_id' => $link->getUser()->getId(),
        ]);
    }

    #[OA\Delete(
        path: '/api/links/{id}',
        summary: 'Supprime un lien',
        tags: ['Links'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Lien supprimé'
            ),
            new OA\Response(
                response: 404,
                description: 'Lien non trouvé'
            )
        ]
    )]
    #[Route('/api/links/{id}', name: 'api_link_delete', methods: ['DELETE'])]
    public function deleteLink(
        int $id,
        EntityManagerInterface $entityManager
    ): Response {
        $link = $entityManager->getRepository(Link::class)->find($id);
        if (!$link) {
            return $this->json(['error' => 'Link not found'], 404);
        }

        $entityManager->remove($link);
        $entityManager->flush();

        return new Response(null, 204);
    }
}
