<?php

namespace Tests\Feature;

use App\Models\OrganizationDetails;
use App\Models\Organizations;
use App\Models\Publications;
use App\Models\States;
use App\Models\StaticPageItems;
use App\Models\StaticPages;
use Tests\TestCase;

/**
 * Every public GET route renders.
 *
 * The conversion moved data between controllers and views on most of these
 * pages, and a missing view variable is a 500 that no unit test would catch.
 *
 * The three detail routes seed their own row rather than reaching for whatever
 * the seeder happened to leave behind — a test that skips on an empty table is
 * not coverage, and for the library and career pages this is the only coverage
 * there is.
 */
class PublicRouteSmokeTest extends TestCase
{
    private const PREFIX = 'Route Smoke ';

    private ?Organizations $organization = null;

    protected function tearDown(): void
    {
        $this->organization = null;

        Publications::where('title', 'like', self::PREFIX . '%')
            ->get()
            ->each(function (Publications $publication) {
                $publication->organizations()->detach();
                $publication->delete();
            });

        Organizations::where('name', 'like', self::PREFIX . '%')
            ->get()
            ->each(function (Organizations $organization) {
                OrganizationDetails::where('organization_id', $organization->id)->delete();
                $organization->publications()->detach();
                $organization->delete();
            });

        StaticPageItems::where('title', 'like', self::PREFIX . '%')->delete();
        StaticPages::where('title', 'like', self::PREFIX . '%')->delete();

        parent::tearDown();
    }

    public static function publicRoutes(): array
    {
        return [
            'home' => ['/'],
            'home alias' => ['/home'],
            'index alias' => ['/index'],
            'login' => ['/login'],
            'register' => ['/register'],
            'forgot password' => ['/password-reset'],
            'reset password' => ['/password-reset/any-token'],
            'about' => ['/about-us'],
            'contact' => ['/contact-us'],
            'support us' => ['/support-us'],
            'partners' => ['/our-partners'],
            'privacy' => ['/privacy-policy'],
            'terms' => ['/terms-conditions'],
            'career hub' => ['/career-success-hub'],
            'search resources' => ['/search-resources'],
            'search with filters' => ['/search-resources?state=Alabama&sort=za&advance=1'],
            'library' => ['/library'],
            'library sorted' => ['/library?order=desc'],
        ];
    }

    /**
     * @dataProvider publicRoutes
     */
    public function test_public_routes_render(string $url): void
    {
        $this->get($url)->assertSuccessful();
    }

    public function test_an_organization_detail_page_renders(): void
    {
        $this->get('/organization-details/' . $this->organization()->id)->assertOk();
    }

    public function test_a_library_detail_page_renders(): void
    {
        $publication = new Publications();
        $publication->title = self::PREFIX . 'Publication';
        $publication->organization_id = $this->organization()->id;
        $publication->description = 'A publication seeded by the route smoke test.';
        $publication->state = $this->state()->name;
        $publication->status = 'active';
        $publication->save();

        $this->get('/library/' . $publication->id)->assertOk();
    }

    public function test_a_career_topic_page_renders(): void
    {
        // The controller resolves the hub by slug and 404s unless the topic
        // hangs off that exact row, so reuse a seeded hub when there is one.
        $page = StaticPages::where('slug', 'career-success-hub')->first();

        if (!$page) {
            $page = new StaticPages();
            $page->title = self::PREFIX . 'Career Hub';
            $page->slug = 'career-success-hub';
            $page->description = 'Seeded by the route smoke test.';
            $page->status = 'active';
            $page->save();
        }

        $topic = new StaticPageItems();
        $topic->page_id = $page->id;
        $topic->title = self::PREFIX . 'Topic';
        $topic->description = 'A career topic seeded by the route smoke test.';
        $topic->order = 1;
        $topic->save();

        $this->get('/career-success-hub/topic/' . $topic->id)->assertOk();
    }

    public function test_a_career_topic_from_another_page_is_a_404(): void
    {
        $other = new StaticPages();
        $other->title = self::PREFIX . 'Other Page';
        $other->slug = 'route-smoke-other';
        $other->status = 'active';
        $other->save();

        $topic = new StaticPageItems();
        $topic->page_id = $other->id;
        $topic->title = self::PREFIX . 'Foreign Topic';
        $topic->description = 'Belongs to a different static page.';
        $topic->order = 1;
        $topic->save();

        $this->get('/career-success-hub/topic/' . $topic->id)->assertNotFound();
    }

    private function state(): States
    {
        $state = States::orderBy('name')->first();

        if (!$state) {
            $this->markTestSkipped('States are not seeded.');
        }

        return $state;
    }

    private function organization(): Organizations
    {
        if ($this->organization) {
            return $this->organization;
        }

        $organization = new Organizations();
        $organization->name = self::PREFIX . 'Org';
        $organization->type = 'government';
        $organization->phone = '5551112222';
        $organization->email = 'route-smoke-org@example.com';
        $organization->website = 'https://example.com';
        $organization->category = json_encode([]);
        $organization->target_population = 'Adult';
        $organization->service_area_type = 'state';
        $organization->service_area = $this->state()->name;
        $organization->status = 'active';
        $organization->save();

        $details = new OrganizationDetails();
        $details->organization_id = $organization->id;
        $details->physical_address_1 = '1 Main St';
        $details->physical_city = 'Testville';
        $details->physical_state = $this->state()->name;
        $details->physical_postal_code = '35004';
        $details->latitude = '33.0';
        $details->longitude = '-86.0';
        $details->service_description = 'Route smoke description';
        $details->save();

        return $this->organization = $organization;
    }

    public function test_an_unknown_organization_is_a_404_not_a_500(): void
    {
        $this->get('/organization-details/99999999')->assertNotFound();
    }
}
