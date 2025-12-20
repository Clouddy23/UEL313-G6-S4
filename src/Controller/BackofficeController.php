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

final class BackofficeController extends AbstractController
{
    /**
     * Backoffice pour la gestion des utilisateurs et des liens
     * @route("/backoffice", name="app_backoffice", methods={"GET"})
     */
    #[Route('/backoffice', name: 'app_backoffice', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $activeTab = $request->query->get('tab', 'links');

        if (!$this->isGranted('ROLE_ADMIN') && $activeTab === 'users') {
            $activeTab = 'links';
        }

        if ($this->isGranted('ROLE_ADMIN') && !$request->query->has('tab')) {
            $activeTab = 'users';
        }

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

        $linkRepository = $entityManager->getRepository(Link::class);
        $links = $linkRepository->createQueryBuilder('l')
            ->leftJoin('l.user', 'u')
            ->leftJoin('l.tags', 't')
            ->addSelect('u', 't')
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $tags = $entityManager->getRepository(Tag::class)->findAll();

        return $this->render('backoffice.html.twig', [
            'activeTab' => $activeTab,
            'users' => $users,
            'links' => $links,
            'tags' => $tags,
        ]);
    }

    /**
     * Suppression d'un utilisateur
     * @route("/backoffice/user/{id}/delete", name="app_user_delete", methods={"POST"})
     */
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

    /**
     * Création d'un utilisateur
     * @route("/backoffice/user/create", name="app_user_create", methods={"GET", "POST"})
     */
    #[Route('/backoffice/user/create', name: 'app_user_create', methods: ['GET', 'POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('GET')) {
            return $this->render('admin/user_create.html.twig');
        }

        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $roles = $request->request->all('roles') ?: ['ROLE_USER'];

        if (empty($username) || empty($password)) {
            $this->addFlash('error', 'Le nom d\'utilisateur et le mot de passe sont obligatoires.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'users']);
        }

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

    /**
     * Édition d'un utilisateur
     * @route("/backoffice/user/{id}/edit", name="app_user_edit", methods={"GET", "POST"})
     */
    #[Route('/backoffice/user/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function editUser(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'users']);
        }

        if ($request->isMethod('GET')) {
            return $this->render('admin/user_edit.html.twig', [
                'user' => $user,
            ]);
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

    /**
     * Suppression d'un lien
     * @route("/backoffice/link/{id}/delete", name="app_link_delete", methods={"POST"})
     */
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

    /**
     * Création d'un lien
     * @route("/backoffice/link/create", name="app_link_create", methods={"GET", "POST"})
     */
    #[Route('/backoffice/link/create', name: 'app_link_create', methods: ['GET', 'POST'])]
    public function createLink(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($request->isMethod('GET')) {
            $tags = $entityManager->getRepository(Tag::class)->findAll();
            return $this->render('admin/link_create.html.twig', [
                'tags' => $tags,
            ]);
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

        $link = new Link();
        $link->setTitle($title);
        $link->setUrl($url);
        $link->setDesc($desc);
        $link->setUser($this->getUser());
        $link->setCreatedAt(new \DateTime());

        $tagRepository = $entityManager->getRepository(Tag::class);

        if (!empty($newTagName)) {
            $existingTag = $tagRepository->findOneBy(['name' => $newTagName]);
            if (!$existingTag) {
                $newTag = new Tag();
                $newTag->setName($newTagName);
                $entityManager->persist($newTag);
                $entityManager->flush();
                $selectedTags[] = $newTag->getId();
            } else {
                $selectedTags[] = $existingTag->getId();
            }
        }

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

    /**
     * Édition d'un lien
     * @route("/backoffice/link/{id}/edit", name="app_link_edit", methods={"GET", "POST"})
     */
    #[Route('/backoffice/link/{id}/edit', name: 'app_link_edit', methods: ['GET', 'POST'])]
    public function editLink(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $link = $entityManager->getRepository(Link::class)->find($id);
        if (!$link) {
            $this->addFlash('error', 'Lien introuvable.');
            return $this->redirectToRoute('app_backoffice', ['tab' => 'links']);
        }

        if ($request->isMethod('GET')) {
            $tags = $entityManager->getRepository(Tag::class)->findAll();
            return $this->render('admin/link_edit.html.twig', [
                'link' => $link,
                'tags' => $tags,
            ]);
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

        foreach ($link->getTags() as $tag) {
            $link->removeTag($tag);
        }

        $tagRepository = $entityManager->getRepository(Tag::class);

        if (!empty($newTagName)) {
            $existingTag = $tagRepository->findOneBy(['name' => $newTagName]);
            if (!$existingTag) {
                $newTag = new Tag();
                $newTag->setName($newTagName);
                $entityManager->persist($newTag);
                $entityManager->flush();
                $selectedTags[] = $newTag->getId();
            } else {
                $selectedTags[] = $existingTag->getId();
            }
        }

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
