<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Link;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

class RssFeedController extends AbstractController
{
    #[Route('/feed', name: 'rss_feed', methods: ['GET'])]
    public function rssFeed(EntityManagerInterface $entityManager, Request $request): Response
    {
        try {
            // Récupérer les 15 derniers liens ajoutés (ordonnés par ID décroissant)
            $dql = "SELECT l, u FROM App\Entity\Link l LEFT JOIN l.user u ORDER BY l.id DESC";
            $query = $entityManager->createQuery($dql);
            $query->setMaxResults(15);
            $links = $query->getResult();

            // Générer le XML du flux RSS
            $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $rss .= '<rss version="2.0">' . "\n";
            $rss .= '  <channel>' . "\n";
            $rss .= '    <title>Watson - Derniers liens</title>' . "\n";
            $rss .= '    <link>' . htmlspecialchars($request->getSchemeAndHttpHost()) . '</link>' . "\n";
            $rss .= '    <description>Les 15 derniers liens ajoutés sur Watson</description>' . "\n";
            $rss .= '    <language>fr-FR</language>' . "\n";

            foreach ($links as $link) {
                $rss .= '    <item>' . "\n";
                $rss .= '      <title>' . htmlspecialchars($link->getTitle()) . '</title>' . "\n";
                $rss .= '      <link>' . htmlspecialchars($link->getUrl()) . '</link>' . "\n";
                $rss .= '      <description>' . htmlspecialchars($link->getDesc() ?: 'Aucune description') . '</description>' . "\n";
                if ($link->getUser()) {
                    $rss .= '      <author>' . htmlspecialchars($link->getUser()->getLogin()) . '</author>' . "\n";
                }
                $rss .= '    </item>' . "\n";
            }

            $rss .= '  </channel>' . "\n";
            $rss .= '</rss>';

            // Retourner le flux RSS
            return new Response($rss, 200, ['Content-Type' => 'application/rss+xml; charset=utf-8']);
        } catch (\Exception $e) {
            return new Response('RSS Feed Error: ' . $e->getMessage(), 500, ['Content-Type' => 'text/plain']);
        }
    }
}
