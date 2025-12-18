<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

use Nelmio\ApiDocBundle\Attribute\Security; // A utiliser si des routes nécessitent une authentification (si on a le temps de mettre cela en place...)
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

final class UserController extends AbstractController
{
    #[OA\Get(
        path: '/api/users',
        summary: 'Retourne la liste de tous les utilisateurs',
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retourne la liste de tous les utilisateurs',
            )
        ]
    )]
    #[Route('/api/users', name: 'api_user_list', methods: ['GET'])]
    public function apiListUsers(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();
        return $this->json(['users' => $users]); // For API test purposes
    }

    //-- WEB PAGE RENDERING --
    #[Route('/users', name: 'user_list', methods: ['GET'])]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();

        return $this->render('users/list.html.twig', ['users' => $users]); // For web page rendering
    }

    #[OA\Post(
        path: '/api/users',
        summary: 'Ajoute un nouvel utilisateur',
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Retourne les informations de l\'utilisateur créé',
            ),
            new OA\Response(
                response: 400,
                description: 'Données invalides'
            )
        ]
    )]
    #[Route('/api/users', name: 'api_user_add', methods: ['POST'])]
    public function addUser(EntityManagerInterface $entityManager, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['username'], $data['password'])) {
            return $this->json(['error' => 'username and password are required'], 400);
        }

        $existing = $entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        if ($existing) {
            return $this->json(['error' => 'username already exists'], 400);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword($data['password']); // Password should be hashed
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ], 201);
    }

    #[OA\Put(
        path: '/api/users/{id}',
        summary: 'Modifie un utilisateur existant',
        tags: ['Users'],
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
                    new OA\Property(property: 'username', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur modifié',
            ),
            new OA\Response(
                response: 404,
                description: 'Utilisateur non trouvé'
            )
        ]
    )]
    #[Route('/api/users/{id}', name: 'api_user_update', methods: ['PUT'])]
    public function updateUser(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[OA\Delete(
        path: '/api/users/{id}',
        summary: 'Supprime un utilisateur',
        tags: ['Users'],
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
                description: 'Utilisateur supprimé'
            ),
            new OA\Response(
                response: 404,
                description: 'Utilisateur non trouvé'
            )
        ]
    )]
    #[Route('/api/users/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    public function deleteUser(
        int $id,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new Response(null, 204);
    }
}
