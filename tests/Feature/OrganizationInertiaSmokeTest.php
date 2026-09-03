<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\OrganizationDetails;
use App\Models\Organizations;
use App\Models\PointOfContacts;
use App\Models\States;
use App\Models\User;
use Tests\TestCase;

/**
 * Covers the Inertia/Vue conversion of the admin organization form: create and
 * edit render Vue pages, and store/update answer with either session validation
 * errors or an X-Inertia-Location hand-off to the (still Blade) index.
 *
 * Runs against whatever database is configured and cleans up after itself, so
 * it needs the app's baseline seed data (admin user, states, categories).
 */
class OrganizationInertiaSmokeTest extends TestCase
{
    private const NAME_PREFIX = 'Inertia Smoke ';

    protected function tearDown(): void
    {
        Organizations::where('name', 'like', self::NAME_PREFIX . '%')
            ->get()
            ->each(function (Organizations $organization) {
                OrganizationDetails::where('organization_id', $organization->id)->delete();
                PointOfContacts::where('organization_id', $organization->id)->delete();
                $organization->publications()->detach();
                $organization->delete();
            });

        parent::tearDown();
    }

    /**
     * store()/update() skip the SMTP + website ping checks outside `local`, and
     * phpunit.xml pins the environment to `testing`. Flipping it back also turns
     * CSRF verification on, so drop that middleware for these requests.
     */
    private function asLocalEnvironment(): void
    {
        $this->app['env'] = 'local';
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    private function admin(): User
    {
        $admin = User::where('role', 'admin')->where('status', 'active')->first();

        if (!$admin) {
            $this->markTestSkipped('No active admin user seeded.');
        }

        return $admin;
    }

    private function firstState(): States
    {
        $state = States::orderBy('name')->first();

        if (!$state) {
            $this->markTestSkipped('States are not seeded.');
        }

        return $state;
    }

    private function payload(array $overrides = []): array
    {
        $category = Categories::orderBy('id')->first();

        if (!$category) {
            $this->markTestSkipped('Categories are not seeded.');
        }

        return array_merge([
            'organization_name' => self::NAME_PREFIX . 'Org',
            'organization_type' => 'government',
            'email' => 'inertia-smoke@example.com',
            'phone' => '5551112222',
            'website' => 'https://example.com',
            'service_categories' => [$category->id],
            'service_area' => 'state',
            'service_state' => $this->firstState()->name,
            'target_population' => 'Adult',
            'service_description' => 'Smoke test',
            'point_of_contact_pronouns' => 'They/Them',
            'point_of_contact_first_name' => 'Sam',
            'point_of_contact_last_name' => 'Doe',
            'point_of_contact_email' => 'sam@example.com',
            'point_of_contact_phone' => '5559876543',
            'address_1' => '1 Main St',
            'city' => 'Testville',
            'state' => $this->firstState()->name,
            'postcode' => '35004',
            'latitude' => '33.0',
            'longitude' => '-86.0',
            'is_member' => 0,
        ], $overrides);
    }

    private function createOrganization(array $overrides = []): Organizations
    {
        $this->asLocalEnvironment();

        $this->actingAs($this->admin())
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/admin/organizations', $this->payload($overrides))
            ->assertStatus(409);

        return Organizations::where('name', 'like', self::NAME_PREFIX . '%')
            ->orderByDesc('id')
            ->firstOrFail();
    }

    public function test_create_renders_the_vue_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/organizations/create');

        $response->assertOk();
        // The page object is embedded as JSON in `data-page`, so slashes are escaped.
        $response->assertSee('Organizations\\/Create', false);
    }

    public function test_store_rejects_invalid_input_with_field_errors(): void
    {
        $this->asLocalEnvironment();

        $response = $this->actingAs($this->admin())
            ->from('/admin/organizations/create')
            ->post('/admin/organizations', $this->payload([
                'organization_name' => '',
                'postcode' => 'nope',
                'latitude' => '',
                'longitude' => '',
            ]));

        $response->assertRedirect('/admin/organizations/create');
        $response->assertSessionHasErrors(['organization_name', 'postcode', 'latitude', 'longitude']);

        $this->assertSame(
            'Please select a valid address from the suggestions.',
            session('errors')->first('latitude')
        );
    }

    public function test_store_creates_and_hands_back_an_inertia_location(): void
    {
        $organization = $this->createOrganization();

        $this->assertSame('Organization Details added successfully', session('success'));
        $this->assertSame('state', $organization->service_area_type);
        $this->assertSame($this->firstState()->name, $organization->service_area);
        $this->assertNotNull(OrganizationDetails::where('organization_id', $organization->id)->first());
        $this->assertNotNull(PointOfContacts::where('organization_id', $organization->id)->first());
    }

    public function test_update_saves_and_hands_back_an_inertia_location(): void
    {
        $organization = $this->createOrganization();

        $response = $this->actingAs($this->admin())
            ->withHeaders(['X-Inertia' => 'true'])
            ->put('/admin/organizations/' . $organization->id, $this->payload([
                'organization_name' => self::NAME_PREFIX . 'Renamed',
                'service_area' => 'national',
                'service_state' => '',
                'is_member' => 1,
            ]));

        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', url('/admin/organizations'));
        $this->assertSame('Organization Details updated successfully', session('success'));

        $organization->refresh();
        $this->assertSame(self::NAME_PREFIX . 'Renamed', $organization->name);
        $this->assertSame('national', $organization->service_area_type);
        $this->assertSame('National', $organization->service_area);
        $this->assertSame('1', (string) $organization->is_member);
    }

    public function test_edit_renders_the_vue_page_with_prefilled_values(): void
    {
        $organization = $this->createOrganization();

        // createOrganization() leaves X-Inertia on the default headers; keeping it
        // on a GET without a version header would get a 409 asset-mismatch reload.
        $this->flushHeaders();

        $response = $this->actingAs($this->admin())
            ->get('/admin/organizations/' . $organization->id . '/edit');

        $response->assertOk();
        $response->assertSee('Organizations\\/Edit', false);
        $response->assertSee(self::NAME_PREFIX . 'Org', false);
        // Phone reaches the form as bare digits; the Vue input applies the mask.
        $response->assertSee('5551112222', false);
    }
}
