<?php

namespace Tests\Feature;

use App\Models\Queries;
use App\Models\States;
use Tests\TestCase;

/**
 * The public marketing and directory pages.
 *
 * These stay server rendered — they are the crawl path into the site — so the
 * assertions here are that the copy is still in the HTML and that the
 * interactive parts are mounted as islands rather than inline jQuery.
 */
class PublicPagesVueTest extends TestCase
{
    protected function tearDown(): void
    {
        Queries::where('email', 'like', 'public-smoke-%@example.com')->delete();

        parent::tearDown();
    }

    public function test_the_home_page_mounts_the_search_and_map_islands(): void
    {
        $response = $this->get('/home');

        $response->assertOk();
        $response->assertSee('data-vue-island="resource-search-form"', false);
        $response->assertSee('data-vue-island="resource-map"', false);

        // The 111-line Google Maps bootstrap this page carried is gone; the
        // island loads the SDK itself.
        $response->assertDontSee('maps.googleapis.com/maps/api/js', false);
        $response->assertDontSee('function initMap()', false);
    }

    /** The home box is the search page's, minus the extra filters. */
    public function test_the_home_search_box_hides_the_advanced_filters(): void
    {
        $props = $this->islandProps($this->get('/home')->getContent(), 'resource-search-form');

        $this->assertFalse($props['showAdvanced']);
        $this->assertNotEmpty($props['states']);
        $this->assertSame(url('search-resources'), $props['action']);
    }

    public function test_the_library_keeps_its_listing_in_html_and_mounts_the_filters(): void
    {
        $response = $this->get('/library');

        $response->assertOk();
        // Two mounts of one island: the sort control and the location list.
        $response->assertSee('&quot;section&quot;:&quot;sort&quot;', false);
        $response->assertSee('&quot;section&quot;:&quot;locations&quot;', false);

        // select2 and the hidden-input-plus-submit dance are gone.
        $response->assertDontSee('select2(', false);
        $response->assertDontSee('hidden_state', false);
    }

    public function test_the_library_filters_carry_the_current_selection(): void
    {
        $state = States::orderBy('name')->first();

        if (!$state) {
            $this->markTestSkipped('States are not seeded.');
        }

        $content = $this->get('/library?state=' . $state->id . '&order=desc')->getContent();
        $props = $this->islandProps($content, 'library-filters');

        $this->assertSame((string) $state->id, $props['selectedState']);
        $this->assertSame('desc', $props['order']);
        $this->assertSame(url('library'), $props['baseUrl']);
    }

    public function test_the_career_hub_is_one_island_with_its_segments_as_props(): void
    {
        $response = $this->get('/career-success-hub');

        $response->assertOk();
        $response->assertSee('data-vue-island="career-hub"', false);

        $props = $this->islandProps($response->getContent(), 'career-hub');

        $this->assertCount(4, $props['segments']);
        $this->assertSame([1, 2, 3, 4], array_column($props['segments'], 'index'));
        $this->assertStringContainsString('__TOPIC_ID__', $props['topicUrlTemplate']);

        foreach ($props['segments'] as $segment) {
            $this->assertNotSame('', $segment['title']);
            $this->assertArrayHasKey('viewBox', $segment['curve']);
            $this->assertArrayHasKey('topics', $segment);
        }

        // The 212 lines of jQuery that drove the wheel are gone.
        $response->assertDontSee('lastClickedSegment', false);
        $response->assertDontSee('adjustCircleSpacing', false);
    }

    public function test_the_contact_page_mounts_the_form_island(): void
    {
        $response = $this->get('/contact-us');

        $response->assertOk();
        $response->assertSee('data-vue-island="contact-form"', false);
        $response->assertDontSee("$('#contact_form')", false);
    }

    public function test_the_contact_form_stores_a_query(): void
    {
        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\RecaptchaProtection::class,
        ])
            ->postJson('/contact-us', [
                'first_name' => 'Public',
                'last_name' => 'Smoke',
                'organization' => 'Smoke Org',
                'email' => 'public-smoke-a@example.com',
                'message' => 'Hello there',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $query = Queries::where('email', 'public-smoke-a@example.com')->firstOrFail();
        $this->assertSame('Hello there', $query->message);
    }

    /** `email` used to be `required` with no format rule at all. */
    public function test_the_contact_form_rejects_a_malformed_email(): void
    {
        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\RecaptchaProtection::class,
        ])
            ->postJson('/contact-us', [
                'first_name' => 'Public',
                'last_name' => 'Smoke',
                'organization' => 'Smoke Org',
                'email' => 'not-an-email',
                'message' => 'Hello there',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertSame(0, Queries::where('first_name', 'Public')->count());
    }

    public function test_the_contact_form_returns_field_errors_the_island_can_read(): void
    {
        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\RecaptchaProtection::class,
        ])
            ->postJson('/contact-us', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'organization', 'email', 'message']);
    }

    /**
     * The share sheet is included on every page that offers sharing, and it is
     * @once, so it must appear exactly one time.
     */
    public function test_the_share_island_is_mounted_once_per_page(): void
    {
        foreach (['/library', '/career-success-hub'] as $url) {
            $content = $this->get($url)->getContent();

            $this->assertSame(
                1,
                substr_count($content, 'data-vue-island="share-modal"'),
                "Share island count is wrong on {$url}.",
            );
        }
    }

    private function islandProps(string $html, string $island): array
    {
        $pattern = '/data-vue-island="' . preg_quote($island, '/') . '"\s+data-vue-props="([^"]*)"/';

        $this->assertMatchesRegularExpression($pattern, $html, "No props found for island [{$island}].");

        preg_match($pattern, $html, $matches);

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);
    }
}
