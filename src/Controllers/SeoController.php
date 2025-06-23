<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use App\Services\ArticleService;

class SeoController
{
//    public function sitemap($request, Response $response, $args): Response
//    {
//        $articles = ArticleService::getIndex();
/*        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';*/
//        $domain = $_ENV['APP_URL'] ?? 'https://your-domain.com';
//        foreach ($articles as $a) {
//            $xml .= '<url>';
//            $xml .= '<loc>' . htmlspecialchars($domain . '/article/' . $a['id']) . '</loc>';
//            $xml .= '<lastmod>' . date('c', strtotime($a['date'])) . '</lastmod>';
//            $xml .= '<changefreq>daily</changefreq>';
//            $xml .= '<priority>0.7</priority>';
//            $xml .= '</url>';
//        }
//        $xml .= '</urlset>';
//        $response->getBody()->write($xml);
//        return $response->withHeader('Content-Type', 'application/xml');
//    }
    public function sitemap($request, Response $response, $args): Response
    {
        $articles = ArticleService::getIndex();
        $domain = $_ENV['APP_URL'] ?? 'https://your-domain.com';
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // 文章详情页
        foreach ($articles as $a) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($domain . '/article/' . $a['id']) . '</loc>';
            $xml .= '<lastmod>' . date('c', strtotime($a['date'])) . '</lastmod>';
            $xml .= '<changefreq>daily</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';
        }

        // 标签聚合页
        $allTags = [];
        foreach ($articles as $a) {
            if (!empty($a['tags'])) {
                foreach ($a['tags'] as $tag) $allTags[$tag] = true;
            }
        }
        foreach (array_keys($allTags) as $tag) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($domain . '/tags/' . urlencode($tag)) . '</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.5</priority>';
            $xml .= '</url>';
        }

        // 热门爆料页
        $xml .= '<url><loc>' . $domain . '/hot</loc><changefreq>hourly</changefreq><priority>0.8</priority></url>';

        $xml .= '</urlset>';
        $response->getBody()->write($xml);
        return $response->withHeader('Content-Type', 'application/xml');
    }
}