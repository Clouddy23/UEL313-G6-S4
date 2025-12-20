<?php

namespace App\Controller\api;

use App\Entity\Tag;
use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TagController extends AbstractController
{
    #[OA\Get(
        path: '/api/tags',
        summary: 'Retourne la liste de tous les tags',
        tags: ['Tags'],
        responses: [new OA\Response(response: 200, description: 'Liste des tags')]
    )]
    #[Route('/api/tags', name: 'api_tag_list', methods: ['GET'])]
    public function apiListTags(EntityManagerInterface $em): Response
    {
        $tags = $em->getRepository(Tag::class)->findAll();

        $data = array_map(fn(Tag $tag) => [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'link_ids' => array_map(fn(Link $l) => $l->getId(), $tag->getLinks()->toArray()),
        ], $tags);

        return $this->json(['tags' => $data]);
    }

    #[OA\Get(
        path: '/api/tags/{id}',
        summary: 'Retourne un tag par son ID',
        tags: ['Tags'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Tag trouvé'),
            new OA\Response(response: 404, description: 'Tag non trouvé'),
        ]
    )]
    #[Route('/api/tags/{id}', name: 'api_tag_show', methods: ['GET'])]
    public function showTag(int $id, EntityManagerInterface $em): Response
    {
        $tag = $em->getRepository(Tag::class)->find($id);
        if (!$tag) return $this->json(['error' => 'Tag not found'], 404);

        return $this->json([
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'link_ids' => array_map(fn(Link $l) => $l->getId(), $tag->getLinks()->toArray()),
        ]);
    }

    #[OA\Post(
        path: '/api/tags',
        summary: 'Crée un nouveau tag',
        tags: ['Tags'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [new OA\Property(property: 'name', type: 'string', example: 'symfony')]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tag créé'),
            new OA\Response(response: 400, description: 'Données invalides / Tag déjà existant'),
        ]
    )]
    #[Route('/api/tags', name: 'api_tag_create', methods: ['POST'])]
    public function createTag(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['name']) || trim($data['name']) === '') {
            return $this->json(['error' => 'name is required'], 400);
        }

        // Normalisation du nom du tag en minuscules et suppression des espaces inutiles pour éviter les doublons à cause de la casse ou des espaces
        $name = mb_strtolower(trim($data['name']));
        $existing = $em->getRepository(Tag::class)->findOneBy(['name' => $name]);
        if ($existing) return $this->json(['error' => 'Tag already exists'], 400);

        $tag = new Tag();
        $tag->setName($name);

        $em->persist($tag);
        $em->flush();

        return $this->json(['id' => $tag->getId(), 'name' => $tag->getName()], 201);
    }

    #[OA\Put(
        path: '/api/tags/{id}',
        summary: 'Modifie un tag existant',
        tags: ['Tags'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [new OA\Property(property: 'name', type: 'string', example: 'doctrine')]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Tag modifié'),
            new OA\Response(response: 404, description: 'Tag non trouvé'),
            new OA\Response(response: 400, description: 'Nom déjà utilisé / invalide'),
        ]
    )]
    #[Route('/api/tags/{id}', name: 'api_tag_update', methods: ['PUT'])]
    public function updateTag(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $tag = $em->getRepository(Tag::class)->find($id);
        if (!$tag) return $this->json(['error' => 'Tag not found'], 404);

        $data = json_decode($request->getContent(), true);
        if (!isset($data['name'])) return $this->json(['id' => $tag->getId(), 'name' => $tag->getName()]);

        // Normalisation du nom du tag en minuscules et suppression des espaces inutiles pour éviter les doublons à cause de la casse ou des espaces
        $name = mb_strtolower(trim($data['name']));
        if ($name === '') return $this->json(['error' => 'name cannot be empty'], 400);

        $existing = $em->getRepository(Tag::class)->findOneBy(['name' => $name]);
        if ($existing && $existing->getId() !== $tag->getId()) {
            return $this->json(['error' => 'Tag name already used'], 400);
        }

        $tag->setName($name);
        $em->flush();

        return $this->json(['id' => $tag->getId(), 'name' => $tag->getName()]);
    }

    #[OA\Delete(
        path: '/api/tags/{id}',
        summary: 'Supprime un tag',
        tags: ['Tags'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Tag supprimé'),
            new OA\Response(response: 404, description: 'Tag non trouvé'),
        ]
    )]
    #[Route('/api/tags/{id}', name: 'api_tag_delete', methods: ['DELETE'])]
    public function deleteTag(int $id, EntityManagerInterface $em): Response
    {
        $tag = $em->getRepository(Tag::class)->find($id);
        if (!$tag) return $this->json(['error' => 'Tag not found'], 404);

        $em->remove($tag);
        $em->flush();

        return new Response(null, 204);
    }

    #[OA\Post(
        path: '/api/links/{linkId}/tags/{tagId}',
        summary: 'Associe un tag existant à un lien existant',
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'linkId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'tagId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Association effectuée'),
            new OA\Response(response: 404, description: 'Lien ou tag non trouvé'),
        ]
    )]
    #[Route('/api/links/{linkId}/tags/{tagId}', name: 'api_link_add_tag', methods: ['POST'])]
    public function addTagToLink(int $linkId, int $tagId, EntityManagerInterface $em): Response
    {
        $link = $em->getRepository(Link::class)->find($linkId);
        if (!$link) return $this->json(['error' => 'Link not found'], 404);

        $tag = $em->getRepository(Tag::class)->find($tagId);
        if (!$tag) return $this->json(['error' => 'Tag not found'], 404);

        // Vérification si le tag est déjà associé au lien ça évite les doublons
        if ($link->getTags()->contains($tag)) {
            return $this->json([
                'message' => 'Tag already linked to this link',
                'link_id' => $link->getId(),
                'tag_ids' => array_map(fn(Tag $t) => $t->getId(), $link->getTags()->toArray()),
            ], 200);
        }

        $link->addTag($tag);


        $link->addTag($tag);
        $em->flush();

        return $this->json([
            'link_id' => $link->getId(),
            'tag_ids' => array_map(fn(Tag $t) => $t->getId(), $link->getTags()->toArray()),
        ]);
    }

    #[OA\Delete(
        path: '/api/links/{linkId}/tags/{tagId}',
        summary: 'Dissocie un tag d’un lien',
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'linkId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'tagId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Dissociation effectuée'),
            new OA\Response(response: 404, description: 'Lien ou tag non trouvé'),
        ]
    )]
    #[Route('/api/links/{linkId}/tags/{tagId}', name: 'api_link_remove_tag', methods: ['DELETE'])]
    public function removeTagFromLink(int $linkId, int $tagId, EntityManagerInterface $em): Response
    {
        $link = $em->getRepository(Link::class)->find($linkId);
        if (!$link) return $this->json(['error' => 'Link not found'], 404);

        $tag = $em->getRepository(Tag::class)->find($tagId);
        if (!$tag) return $this->json(['error' => 'Tag not found'], 404);

        $link->removeTag($tag);
        $em->flush();

        return new Response(null, 204);
    }

    #[OA\Get(
        path: '/api/links/{linkId}/tags',
        summary: 'Liste les tags d’un lien',
        tags: ['Tags'],
        parameters: [new OA\Parameter(name: 'linkId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Liste des tags du lien'),
            new OA\Response(response: 404, description: 'Lien non trouvé'),
        ]
    )]
    #[Route('/api/links/{linkId}/tags', name: 'api_link_tags_list', methods: ['GET'])]
    public function listTagsForLink(int $linkId, EntityManagerInterface $em): Response
    {
        $link = $em->getRepository(Link::class)->find($linkId);
        if (!$link) return $this->json(['error' => 'Link not found'], 404);

        $data = array_map(fn(Tag $tag) => [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
        ], $link->getTags()->toArray());

        return $this->json(['link_id' => $link->getId(), 'tags' => $data]);
    }
}
