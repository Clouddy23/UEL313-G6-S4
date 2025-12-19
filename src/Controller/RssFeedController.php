<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\LinkRepository;
use Symfony\Component\HttpFoundation\Request;

class RssFeedController extends AbstractController
{
    //WEB ROUTE - RSS Feed des 15 derniers liens ajoutés
    #[Route('/feed', name: 'rss_feed', methods: ['GET'])]
    public function rssFeed(LinkRepository $linkRepository, Request $request): Response
    {
        try {
            // Récupérer les 15 derniers liens ajoutés avec leurs utilisateurs et tags
            $links = $linkRepository->findLast15Links();

            // Générer le XML du flux RSS
            $rss = $this->generateRssXml($links, $request);

            // Retourner le flux RSS
            return new Response($rss, 200, ['Content-Type' => 'application/rss+xml; charset=utf-8']);
        } catch (\Exception $e) {
            return new Response('RSS Feed Error: ' . $e->getMessage(), 500, ['Content-Type' => 'text/plain']);
        }
    }

    /**
     * Generate RSS XML from links array
     * @param array $links
     * @param Request $request
     * @return string
     */
    private function generateRssXml(array $links, Request $request): string
    {
        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0">' . "\n";
        $rss .= '  <channel>' . "\n";
        $rss .= '    <title>Watson - Derniers liens</title>' . "\n";
        $rss .= '    <link>' . htmlspecialchars($request->getSchemeAndHttpHost()) . '</link>' . "\n";
        $rss .= '    <description>Les 15 derniers liens ajoutés sur Watson</description>' . "\n";
        $rss .= '    <language>fr-FR</language>' . "\n";
        $rss .= '    <lastBuildDate>' . date('r') . '</lastBuildDate>' . "\n";

        foreach ($links as $link) {
            $rss .= '    <item>' . "\n";
            $rss .= '      <title>' . htmlspecialchars($link->getTitle()) . '</title>' . "\n";
            $rss .= '      <link>' . htmlspecialchars($link->getUrl()) . '</link>' . "\n";
            $rss .= '      <description>' . htmlspecialchars($link->getDesc() ?: 'Aucune description') . '</description>' . "\n";

            if ($link->getUser()) {
                $rss .= '      <author>' . htmlspecialchars($link->getUser()->getUsername()) . '</author>' . "\n";
            }

            // Add creation date if available
            if ($link->getCreatedAt()) {
                $rss .= '      <pubDate>' . $link->getCreatedAt()->format('r') . '</pubDate>' . "\n";
            }

            // Add tags as categories
            if ($link->getTags()->count() > 0) {
                foreach ($link->getTags() as $tag) {
                    $rss .= '      <category>' . htmlspecialchars($tag->getName()) . '</category>' . "\n";
                }
            }

            $rss .= '      <guid>' . htmlspecialchars($request->getSchemeAndHttpHost() . '/links/' . $link->getId()) . '</guid>' . "\n";
            $rss .= '    </item>' . "\n";
        }

        $rss .= '  </channel>' . "\n";
        $rss .= '</rss>';

        return $rss;
    }
}
