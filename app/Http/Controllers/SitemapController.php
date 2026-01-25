<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Define your public pages with priority and change frequency
        $pages = [
            ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => '/how-it-works', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => '/pricing', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => '/contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => '/privacy-policy', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => '/terms', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => '/refund-policy', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => '/support', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['url' => '/faq', 'priority' => '0.6', 'changefreq' => 'weekly'],
        ];

        foreach ($pages as $page) {
            $sitemap .= '  <url>' . "\n";
            $sitemap .= '    <loc>' . URL::to($page['url']) . '</loc>' . "\n";
            $sitemap .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
            $sitemap .= '    <changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
            $sitemap .= '    <priority>' . $page['priority'] . '</priority>' . "\n";
            $sitemap .= '  </url>' . "\n";
        }
        
        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }
}
