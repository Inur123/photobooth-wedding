<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Enums\GalleryVisibility;
use App\Models\Event;
use App\Support\SeoMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $baseUrl = rtrim($request->getSchemeAndHttpHost() ?: config('app.url', SeoMeta::PRODUCTION_URL), '/');
        $today = now()->toAtomString();

        $urls = [
            [
                'loc' => $baseUrl.'/',
                'lastmod' => $today,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => $baseUrl.'/login',
                'lastmod' => $today,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'loc' => $baseUrl.'/register',
                'lastmod' => $today,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $baseUrl.'/terms',
                'lastmod' => $today,
                'changefreq' => 'yearly',
                'priority' => '0.4',
            ],
            [
                'loc' => $baseUrl.'/privacy',
                'lastmod' => $today,
                'changefreq' => 'yearly',
                'priority' => '0.4',
            ],
        ];

        $publicEvents = Event::query()
            ->whereIn('status', [EventStatus::Active, EventStatus::Closed])
            ->where('gallery_visibility', GalleryVisibility::Public)
            ->orderByDesc('is_demo')
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get(['slug', 'is_demo', 'updated_at']);

        foreach ($publicEvents as $event) {
            $urls[] = [
                'loc' => $baseUrl.'/e/'.rawurlencode($event->slug),
                'lastmod' => ($event->updated_at ?? now())->toAtomString(),
                'changefreq' => $event->is_demo ? 'weekly' : 'daily',
                'priority' => $event->is_demo ? '0.9' : '0.8',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $item) {
            $loc = htmlspecialchars($item['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $lastmod = htmlspecialchars($item['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $changefreq = htmlspecialchars($item['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $priority = htmlspecialchars($item['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8');

            $xml .= "    <url>\n";
            $xml .= "        <loc>{$loc}</loc>\n";
            $xml .= "        <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "        <changefreq>{$changefreq}</changefreq>\n";
            $xml .= "        <priority>{$priority}</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= "</urlset>\n";

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
