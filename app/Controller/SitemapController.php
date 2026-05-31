<?php

namespace App\Controller;

class SitemapController
{
    protected int $cacheTtl = 3600;

    public function index()
    {
        $cacheFile = INDEX_DIR . '/sitemap.xml';

        if (!file_exists($cacheFile) || (time() - filemtime($cacheFile)) > $this->cacheTtl) {
            $xml = $this->buildSitemapXml();
            @file_put_contents($cacheFile, $xml, LOCK_EX);
        }

        header('Content-Type: application/xml; charset=UTF-8');
        echo file_get_contents($cacheFile);
    }

    protected function buildSitemapXml(): string
    {

        $routesInfo = \DLight\Application\Router::getRegisteredRoutes();
        $paths = [];

        foreach ($routesInfo['static'] as $method => $map) {
            if ($method !== 'GET') {
                continue;
            }
            foreach ($map as $path => $meta) {
                if (str_contains($path, '{')) {
                    continue;
                }
                $paths[$path] = [
                    'loc' => rtrim(getBaseUrl(), '/') . $path,
                    'lastmod' => date('c'),
                    'changefreq' => 'weekly',
                    'priority' => '0.5'
                ];
            }
        }

        foreach ($routesInfo['names'] as $info) {
            if (!empty($info['api'])) {
                continue;
            }
            $p = $info['path'];
            if (str_contains($p, '{')) {
                continue;
            }
            $loc = rtrim(getBaseUrl(), '/') . $p;
            $paths[$p] = [
                'loc' => $loc,
                'lastmod' => date('c'),
                'changefreq' => 'weekly',
                'priority' => '0.6'
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        foreach ($paths as $info) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($info['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>{$info['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$info['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$info['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        return $xml . ('</urlset>' . PHP_EOL);
    }
}
