<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Link;
use App\Entity\Tag;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Handle search functionality
        $search = $request->query->get('search');

        if ($search) {
            // Use QueryBuilder for search functionality
            $queryBuilder = $entityManager->getRepository(Link::class)->createQueryBuilder('l')
                ->leftJoin('l.user', 'u')
                ->leftJoin('l.tags', 't')
                ->addSelect('u', 't');

            $queryBuilder->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('l.title', ':search'),
                    $queryBuilder->expr()->like('l.desc', ':search'),
                    $queryBuilder->expr()->like('t.name', ':search')
                )
            )
                ->setParameter('search', '%' . $search . '%')
                ->orderBy('l.createdAt', 'DESC');

            $links = $queryBuilder->getQuery()->getResult();
        } else {
            // Get all links with tags and users
            $linkRepository = $entityManager->getRepository(Link::class);
            $links = $linkRepository->createQueryBuilder('l')
                ->leftJoin('l.user', 'u')
                ->leftJoin('l.tags', 't')
                ->addSelect('u', 't')
                ->orderBy('l.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('index.html.twig', [
            'links' => $links,
            'search' => $search
        ]);
    }

    #[Route('/backoffice', name: 'app_backoffice', methods: ['GET'])]
    public function backoffice(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check if user is authenticated
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Get the active tab from query parameter (default to 'links' for non-admin users)
        $activeTab = $request->query->get('tab', 'links');

        // If not admin and trying to access users tab, redirect to links
        if (!$this->isGranted('ROLE_ADMIN') && $activeTab === 'users') {
            $activeTab = 'links';
        }

        // If admin and no tab specified, default to users
        if ($this->isGranted('ROLE_ADMIN') && !$request->query->has('tab')) {
            $activeTab = 'users';
        }

        // Get users data with their links (only for admins)
        $users = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $userRepository = $entityManager->getRepository(User::class);
            $users = $userRepository->createQueryBuilder('u')
                ->leftJoin('u.links', 'l')
                ->addSelect('l')
                ->orderBy('u.id', 'ASC')
                ->getQuery()
                ->getResult();
        }

        // Get links data with related entities (always visible)
        $linkRepository = $entityManager->getRepository(Link::class);
        $links = $linkRepository->createQueryBuilder('l')
            ->leftJoin('l.user', 'u')
            ->leftJoin('l.tags', 't')
            ->addSelect('u', 't')
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Get all available tags for selection
        $tags = $entityManager->getRepository(Tag::class)->findAll();

        return $this->render('backoffice.html.twig', [
            'activeTab' => $activeTab,
            'users' => $users,
            'links' => $links,
            'tags' => $tags,
        ]);
    }

    #[Route('/backoffice/user/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function deleteUser(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'users']);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('app_backoffice', ['tab' => 'users']);
    }

    #[Route('/backoffice/user/create', name: 'app_user_create', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $roles = $request->request->all('roles') ?: ['ROLE_USER'];

        if (empty($username) || empty($password)) {
            $this->addFlash('error', 'Le nom d\'utilisateur et le mot de passe sont obligatoires.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'users']);
        }

        // Check if user already exists
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUser) {
            $this->addFlash('error', 'Un utilisateur avec ce nom d\'utilisateur existe déjà.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'users']);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        $user->setRoles($roles);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur créé avec succès.');
        return $this->redirectToRoute('app_backoffice', ['tab' => 'users']);
    }

    #[Route('/backoffice/user/{id}/edit', name: 'app_user_edit', methods: ['POST'])]
    public function editUser(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'users']);
        }

        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $roles = $request->request->all('roles') ?: ['ROLE_USER'];

        if (empty($username)) {
            $this->addFlash('error', 'Le nom d\'utilisateur est obligatoire.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'users']);
        }

        $user->setUsername($username);
        if (!empty($password)) {
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        }
        $user->setRoles($roles);

        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur modifié avec succès.');
        return $this->redirectToRoute('app_backoffice', ['tab' => 'users']);
    }

    #[Route('/backoffice/link/{id}/delete', name: 'app_link_delete', methods: ['POST'])]
    public function deleteLink(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $link = $entityManager->getRepository(Link::class)->find($id);
        if (!$link) {
            $this->addFlash('error', 'Lien introuvable.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'links']);
        }

        $entityManager->remove($link);
        $entityManager->flush();

        $this->addFlash('success', 'Lien supprimé avec succès.');
        return $this->redirectToRoute('app_backoffice', ['tab' => 'links']);
    }

    #[Route('/backoffice/link/create', name: 'app_link_create', methods: ['POST'])]
    public function createLink(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $title = $request->request->get('title');
        $url = $request->request->get('url');
        $desc = $request->request->get('desc');
        $selectedTags = $request->request->all('tags') ?: [];
        $newTagName = trim($request->request->get('new_tag', ''));

        if (empty($title) || empty($url)) {
            $this->addFlash('error', 'Le titre et l\'URL sont obligatoires.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'links']);
        }

        $link = new Link();
        $link->setTitle($title);
        $link->setUrl($url);
        $link->setDesc($desc);
        $link->setUser($this->getUser());
        $link->setCreatedAt(new \DateTime());

        // Handle tags
        $tagRepository = $entityManager->getRepository(Tag::class);

        // Add new tag if provided
        if (!empty($newTagName)) {
            $existingTag = $tagRepository->findOneBy(['name' => $newTagName]);
            if (!$existingTag) {
                $newTag = new Tag();
                $newTag->setName($newTagName);
                $entityManager->persist($newTag);
                $entityManager->flush(); // Flush to get ID
                $selectedTags[] = $newTag->getId();
            } else {
                $selectedTags[] = $existingTag->getId();
            }
        }

        // Add selected existing tags
        foreach ($selectedTags as $tagId) {
            $tag = $tagRepository->find($tagId);
            if ($tag) {
                $link->addTag($tag);
            }
        }

        $entityManager->persist($link);
        $entityManager->flush();

        $this->addFlash('success', 'Lien créé avec succès.');
        return $this->redirectToRoute('app_backoffice', ['tab' => 'links']);
    }

    #[Route('/backoffice/link/{id}/edit', name: 'app_link_edit', methods: ['POST'])]
    public function editLink(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $link = $entityManager->getRepository(Link::class)->find($id);
        if (!$link) {
            $this->addFlash('error', 'Lien introuvable.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'links']);
        }

        $title = $request->request->get('title');
        $url = $request->request->get('url');
        $desc = $request->request->get('desc');
        $selectedTags = $request->request->all('tags') ?: [];
        $newTagName = trim($request->request->get('new_tag', ''));

        if (empty($title) || empty($url)) {
            $this->addFlash('error', 'Le titre et l\'URL sont obligatoires.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'links']);
        }

        $link->setTitle($title);
        $link->setUrl($url);
        $link->setDesc($desc);

        // Handle tags - remove all existing tags first
        foreach ($link->getTags() as $tag) {
            $link->removeTag($tag);
        }

        // Handle tags
        $tagRepository = $entityManager->getRepository(Tag::class);

        // Add new tag if provided
        if (!empty($newTagName)) {
            $existingTag = $tagRepository->findOneBy(['name' => $newTagName]);
            if (!$existingTag) {
                $newTag = new Tag();
                $newTag->setName($newTagName);
                $entityManager->persist($newTag);
                $entityManager->flush(); // Flush to get ID
                $selectedTags[] = $newTag->getId();
            } else {
                $selectedTags[] = $existingTag->getId();
            }
        }

        // Add selected existing tags
        foreach ($selectedTags as $tagId) {
            $tag = $tagRepository->find($tagId);
            if ($tag) {
                $link->addTag($tag);
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Lien modifié avec succès.');
        return $this->redirectToRoute('app_backoffice', ['tab' => 'links']);
    }
}
