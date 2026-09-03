<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\OrganizationDetails;
use App\Models\Organizations;
use App\Models\SavedSearchResources;
use App\Models\States;
use App\Models\User;
use Tests\TestCase;

/**
 * Covers the public-site half of the Vue conversion:
 *
 *  - /search-resources stays server rendered (result cards are in the HTML) and
 *    mounts Vue islands for the interactive parts.
 *  - /saved-search-view is a full Inertia page, and delete-search is scoped to
 *    the owner.
 */
class FrontendVueSmokeTest extends TestCase
{
    private const NAME_PREFIX = 'Frontend Smoke ';

    protected function tearDown(): void
    {
        Organizations::where('name', 'like', self::NAME_PREFIX . '%')
            ->get()
            ->each(function (Organizations $organization) {
                OrganizationDetails::where('organization_id', $organization->id)->delete();
                $organization->delete();
            });

        SavedSearchResources::where('search_params', 'like', '%' . self::NAME_PREFIX . '%')->delete();
        User::where('email', 'like', 'frontend-smoke-%@example.com')->delete();

        parent::tearDown();
    }

    private function state(): States
    {
        $state = States::orderBy('name')->first();

        if (!$state) {
            $this->markTestSkipped('States are not seeded.');
        }

        return $state;
    }

    /**
     * These models have no $fillable, so build them by assignment rather than
     * create() — mass assignment would silently drop every attribute.
     */
    private function member(string $suffix = 'a'): User
    {
        $user = new User();
        $user->first_name = 'Frontend';
        $user->last_name = 'Smoke';
        $user->name = 'Frontend Smoke';
        $user->email = "frontend-smoke-{$suffix}@example.com";
        $user->role = 'user';
        $user->status = 'active';
        $user->password = bcrypt('secret-Passw0rd');
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private function organization(): Organizations
    {
        $organization = new Organizations();
        $organization->name = self::NAME_PREFIX . 'Org';
        $organization->type = 'government';
        $organization->phone = '5551112222';
        $organization->email = 'frontend-smoke-org@example.com';
        $organization->website = 'https://example.com';
        $organization->category = json_encode([Categories::orderBy('id')->first()?->id]);
        $organization->target_population = 'Adult';
        $organization->service_area_type = 'national';
        $organization->service_area = 'National';
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
        $details->service_description = 'Frontend smoke description';
        $details->save();

        return $organization;
    }

    private function savedSearch(User $user, array $params): SavedSearchResources
    {
        $saved = new SavedSearchResources();
        $saved->user_id = $user->id;
        $saved->search_params = json_encode($params);
        $saved->save();

        return $saved;
    }

    public function test_search_page_renders_results_server_side_for_crawlers(): void
    {
        $organization = $this->organization();

        $response = $this->get('/search-resources');

        $response->assertOk();
        // The card markup — and therefore the link Google follows to the detail
        // page — must be in the HTML, not built by JS.
        $response->assertSee($organization->name, false);
        $response->assertSee('/organization-details/' . $organization->id, false);
        $response->assertSee('Frontend smoke description', false);
        // ...and it must not have become an Inertia page.
        $response->assertDontSee('id="app" data-page', false);
    }

    public function test_search_page_mounts_the_vue_islands(): void
    {
        $organization = $this->organization();

        $response = $this->get('/search-resources');

        $response->assertOk();
        foreach (['resource-search-form', 'resource-map', 'search-result-actions'] as $island) {
            $response->assertSee('data-vue-island="' . $island . '"', false);
        }
        // @vite resolves to the hashed build output, so match the emitted asset.
        $response->assertSee('/build/assets/site-', false);

        // Island props are JSON inside an HTML attribute — assert they survive
        // escaping, since a broken payload fails silently in the browser.
        preg_match_all('/data-vue-props="([^"]*)"/', $response->getContent(), $matches);
        $this->assertNotEmpty($matches[1]);

        foreach ($matches[1] as $raw) {
            $this->assertIsArray(
                json_decode(html_entity_decode($raw, ENT_QUOTES), true),
                'Island props did not decode: ' . $raw
            );
        }

        $mapProps = json_decode(html_entity_decode($matches[1][1], ENT_QUOTES), true);
        $this->assertContains(
            $organization->id,
            array_column($mapProps['locations'], 'org_id'),
            'The new organization is missing from the map island payload.'
        );
    }

    public function test_saved_search_view_is_an_inertia_page(): void
    {
        $user = $this->member();

        $this->savedSearch($user, [
            'state' => $this->state()->name,
            'organization_name' => self::NAME_PREFIX . 'Org',
        ]);

        $response = $this->actingAs($user)->get('/saved-search-view');

        $response->assertOk();
        $response->assertSee('Account\\/SavedSearches', false);
        $response->assertSee('Organization name=' . self::NAME_PREFIX . 'Org', false);
    }

    public function test_delete_search_refuses_another_users_saved_search(): void
    {
        $owner = $this->member('a');
        $intruder = $this->member('b');

        $saved = $this->savedSearch($owner, ['organization_name' => self::NAME_PREFIX . 'Org']);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($intruder)
            ->from('/saved-search-view')
            ->delete('/delete-search/' . $saved->id)
            ->assertRedirect('/saved-search-view');

        $this->assertDatabaseHas('saved_search_resources', ['id' => $saved->id]);
        $this->assertSame('Search Result could not found', session('error'));
    }

    public function test_delete_search_removes_the_owners_saved_search(): void
    {
        $owner = $this->member('a');

        $saved = $this->savedSearch($owner, ['organization_name' => self::NAME_PREFIX . 'Org']);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($owner)
            ->from('/saved-search-view')
            ->delete('/delete-search/' . $saved->id)
            ->assertRedirect('/saved-search-view');

        $this->assertDatabaseMissing('saved_search_resources', ['id' => $saved->id]);
        $this->assertSame('Search Results deleted successfully', session('success'));
    }

    public function test_download_search_404s_for_another_users_saved_search(): void
    {
        $owner = $this->member('a');
        $intruder = $this->member('b');

        $saved = $this->savedSearch($owner, ['organization_name' => self::NAME_PREFIX . 'Org']);

        $this->actingAs($intruder)
            ->get('/download-search/' . $saved->id)
            ->assertNotFound();
    }
}
