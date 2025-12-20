<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Link;

final class HomeController extends AbstractController
{
    /**
     * Page d'accueil affichant les liens avec fonctionnalité de recherche
     * @route("/", name="app_home", methods={"GET"})
     */
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search');

        if ($search) {
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
}
