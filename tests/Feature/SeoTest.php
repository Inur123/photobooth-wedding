<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\GalleryVisibility;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_complete_seo_open_graph_and_json_ld_tags(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:description"', false);
        $response->assertSee('/images/og-image.jpg', false);
        $response->assertSee('/images/og-logo-square.png', false);
        $response->assertSee('/favicon-48x48.png', false);
        $response->assertSee('/site.webmanifest', false);
        $response->assertSee('"@type":"Organization"', false);
        $response->assertSee('"@type":"WebSite"', false);
        $response->assertSee('"@type":"SoftwareApplication"', false);
        $response->assertSee('"@type":"FAQPage"', false);
    }

    public function test_public_event_page_renders_dynamic_whatsapp_and_search_meta_tags(): void
    {
        $event = Event::factory()->create([
            'slug' => 'demo-wedding',
            'party_one_name' => 'Anisa',
            'party_two_name' => 'Rizky',
            'venue' => 'Gedung Arsip Nasional, Jakarta',
            'status' => EventStatus::Active,
            'gallery_visibility' => GalleryVisibility::Public,
        ]);

        $response = $this->get("/e/{$event->slug}");

        $response->assertOk();
        $response->assertSee('Anisa &amp; Rizky — Photobooth &amp; Album Kenangan Digital | titip.moment', false);
        $response->assertSee('Gedung Arsip Nasional, Jakarta', false);
        $response->assertSee('/images/og-image.jpg', false);
        $response->assertSee('/images/og-logo-square.png', false);
        $response->assertSee('content="index, follow', false);
        $response->assertSee('"@type":"Event"', false);
    }

    public function test_owner_event_route_sets_noindex_nofollow(): void
    {
        $event = Event::factory()->create([
            'slug' => 'private-owner-check',
            'owner_access_token' => 'secret-owner-token-123',
            'status' => EventStatus::Active,
        ]);

        $response = $this->get("/e/{$event->slug}/owner/secret-owner-token-123");

        $response->assertOk();
        $response->assertSee('content="noindex, nofollow"', false);
    }

    public function test_sitemap_xml_lists_public_pages_and_public_active_events(): void
    {
        $publicEvent = Event::factory()->create([
            'slug' => 'public-wedding-event',
            'status' => EventStatus::Active,
            'gallery_visibility' => GalleryVisibility::Public,
        ]);

        $privateEvent = Event::factory()->create([
            'slug' => 'private-wedding-event',
            'status' => EventStatus::Active,
            'gallery_visibility' => GalleryVisibility::Private,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('/e/'.$publicEvent->slug, false);
        $response->assertDontSee('/e/'.$privateEvent->slug, false);
    }
}
