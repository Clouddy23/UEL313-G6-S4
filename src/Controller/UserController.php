<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

use Nelmio\ApiDocBundle\Attribute\Security; // A utiliser si des routes nécessitent une authentification (si on a le temps de mettre cela en place)
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user', methods: ['GET'])]
    public function index(): Response
    {
        return new Response('UserController index');
    }

    #[OA\Get(
        path: '/users',
        summary: 'Retourne la liste de tous les utilisateurs',
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retourne la liste de tous les utilisateurs',
            )
        ]
    )]
    #[Route('/users', name: 'user_list', methods: ['GET'])]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();

        return $this->json(['users' => $users]);
    }

    #[OA\Post(
        path: '/users',
        summary: 'Ajoute un nouvel utilisateur',
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password'],
                properties: [
                    new OA\Property(property: 'login', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                    new OA\Property(property: 'firstname', type: 'string'),
                    new OA\Property(property: 'lastname', type: 'string'),
                    new OA\Property(property: 'administrator', type: 'boolean'),
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
    #[Route('/users', name: 'user_add', methods: ['POST'])]
    public function addUser(EntityManagerInterface $entityManager, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['login'], $data['password'])) {
            return $this->json(['error' => 'login and password are required'], 400);
        }

        $existing = $entityManager->getRepository(User::class)->findOneBy(['login' => $data['login']]);
        if ($existing) {
            return $this->json(['error' => 'login already exists'], 400);
        }

        $user = new User();
        $user->setLogin($data['login']);
        $user->setPassword($data['password']); // Password is hashed in the setter
        if (isset($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }
        if (isset($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }
        if (isset($data['administrator'])) {
            $user->setAdministrator((bool)$data['administrator']);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'administrator' => $user->isAdministrator(),
        ], 201);
    }
}
