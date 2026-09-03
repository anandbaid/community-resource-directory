<?php

namespace Tests\Feature;

use App\Models\OrganizationDetails;
use App\Models\Organizations;
use App\Models\SiteSettings;
use App\Models\States;
use App\Models\User;
use Tests\TestCase;

/**
 * The admin organizations list and the read-only view, plus the home-page
 * section builder.
 *
 * The list was a server-side DataTable whose endpoint concatenated every cell
 * as HTML — including status buttons with inline onclick handlers — and shipped
 * it inside JSON. That endpoint and its route are gone.
 */
class OrganizationsIndexVueTest extends TestCase
{
    private const PREFIX = 'OrgList Smoke ';

    /** @var array<int, string> */
    private array $touchedSettings = [];

    private ?User $admin = null;

    protected function tearDown(): void
    {
        Organizations::where('name', 'like', self::PREFIX . '%')
            ->get()
            ->each(function (Organizations $organization) {
                OrganizationDetails::where('organization_id', $organization->id)->delete();
                $organization->publications()->detach();
                $organization->delete();
            });

        foreach ($this->touchedSettings as $name) {
            SiteSettings::where('settings_name', $name)->delete();
        }

        User::where('email', 'like', 'orglist-smoke-%@example.com')->delete();

        $this->touchedSettings = [];
        $this->admin = null;

        parent::tearDown();
    }

    private function admin(): User
    {
        if ($this->admin) {
            return $this->admin;
        }

        $user = new User();
        $user->first_name = 'OrgList';
        $user->last_name = 'Admin';
        $user->name = 'OrgList Admin';
        $user->email = 'orglist-smoke-admin@example.com';
        $user->role = 'admin';
        $user->status = 'active';
        $user->password = bcrypt('Original-Passw0rd!');
        $user->email_verified_at = now();
        $user->save();

        return $this->admin = $user;
    }

    private function state(): States
    {
        $state = States::orderBy('name')->first();

        if (!$state) {
            $this->markTestSkipped('States are not seeded.');
        }

        return $state;
    }

    private function organization(string $suffix = 'Org', array $overrides = []): Organizations
    {
        $organization = new Organizations();
        $organization->name = self::PREFIX . $suffix;
        $organization->type = 'government';
        $organization->phone = '5551112222';
        $organization->email = 'orglist-smoke-' . strtolower($suffix) . '@example.com';
        $organization->website = 'https://example.com';
        $organization->category = json_encode([]);
        $organization->target_population = 'Adult';
        $organization->service_area_type = 'national';
        $organization->service_area = 'National';
        $organization->status = 'active';

        foreach ($overrides as $key => $value) {
            $organization->{$key} = $value;
        }

        $organization->save();

        $details = new OrganizationDetails();
        $details->organization_id = $organization->id;
        $details->physical_address_1 = '1 Main St';
        $details->physical_city = 'Testville';
        $details->physical_state = $this->state()->name;
        $details->physical_postal_code = '35004';
        $details->latitude = '33.0';
        $details->longitude = '-86.0';
        $details->service_description = 'OrgList smoke description';
        $details->save();

        return $organization;
    }

    private function unguarded()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_the_list_renders_rows_as_data(): void
    {
        $organization = $this->organization();

        $response = $this->actingAs($this->admin())->get('/admin/organizations');

        $response->assertOk();
        $response->assertSee('Organizations\\/Index', false);

        $props = $this->pageProps($response->getContent());
        $row = collect($props['organizations']['data'])->firstWhere('id', $organization->id);

        $this->assertNotNull($row);
        $this->assertSame(self::PREFIX . 'Org', $row['name']);
        $this->assertSame('active', $row['status']);
        $this->assertSame(0, $row['spamCount']);
        $this->assertStringContainsString('/admin/organizations/' . $organization->id, $row['editUrl']);

        // The old payload carried rendered markup per cell.
        foreach (['select', 'image', 'actions'] as $htmlKey) {
            $this->assertArrayNotHasKey($htmlKey, $row);
        }
    }

    /** The DataTables endpoint and its route are gone. */
    public function test_the_old_list_endpoint_is_removed(): void
    {
        $this->actingAs($this->admin())->get('/admin/organizations/list')->assertNotFound();
    }

    public function test_the_list_filters_by_search_and_state(): void
    {
        $this->organization('Needle');
        $this->organization('Haystack');

        $found = $this->pageProps(
            $this->actingAs($this->admin())
                ->get('/admin/organizations?search=' . urlencode(self::PREFIX . 'Needle'))
                ->getContent(),
        );

        $names = array_column($found['organizations']['data'], 'name');
        $this->assertContains(self::PREFIX . 'Needle', $names);
        $this->assertNotContains(self::PREFIX . 'Haystack', $names);
        $this->assertSame(self::PREFIX . 'Needle', $found['filters']['search']);

        $byState = $this->pageProps(
            $this->actingAs($this->admin())
                ->get('/admin/organizations?state=' . urlencode($this->state()->name))
                ->getContent(),
        );

        $this->assertSame($this->state()->name, $byState['filters']['state']);
        $this->assertGreaterThan(0, count($byState['organizations']['data']));
    }

    public function test_deleting_an_organization_redirects_with_flash(): void
    {
        $organization = $this->organization();

        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/organizations')
            ->delete('/admin/organizations/' . $organization->id)
            ->assertRedirect('/admin/organizations');

        $this->assertSame('Organization details deleted successfully', session('success'));
        $this->assertNull(Organizations::find($organization->id));
    }

    public function test_bulk_delete_removes_the_selected_rows(): void
    {
        $first = $this->organization('One');
        $second = $this->organization('Two');

        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/organizations')
            ->delete('/admin/organizations/bulk-delete', ['ids' => [$first->id, $second->id]])
            ->assertRedirect('/admin/organizations');

        $this->assertSame('Selected organizations deleted successfully', session('success'));
        $this->assertNull(Organizations::find($first->id));
        $this->assertNull(Organizations::find($second->id));
    }

    public function test_bulk_delete_requires_a_selection(): void
    {
        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/organizations')
            ->delete('/admin/organizations/bulk-delete', ['ids' => []])
            ->assertRedirect('/admin/organizations')
            ->assertSessionHasErrors('ids');
    }

    public function test_the_status_toggle_updates_and_redirects(): void
    {
        $organization = $this->organization();

        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/organizations')
            ->patch('/admin/organizations/statusupdate/' . $organization->id, ['status' => 'inactive'])
            ->assertRedirect('/admin/organizations');

        $this->assertSame('Status updated', session('success'));

        $organization->refresh();
        $this->assertSame('inactive', $organization->status);
    }

    public function test_the_status_toggle_rejects_an_unknown_status(): void
    {
        $organization = $this->organization();

        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/organizations')
            ->patch('/admin/organizations/statusupdate/' . $organization->id, ['status' => 'banana'])
            ->assertSessionHasErrors('status');

        $organization->refresh();
        $this->assertSame('active', $organization->status);
    }

    /** The view screen was a fourth copy of the organization form. */
    public function test_the_show_screen_reuses_the_form_read_only(): void
    {
        $organization = $this->organization();

        $response = $this->actingAs($this->admin())
            ->get('/admin/organizations/' . $organization->id);

        $response->assertOk();
        $response->assertSee('Organizations\\/Show', false);

        $props = $this->pageProps($response->getContent());

        $this->assertSame(self::PREFIX . 'Org', $props['values']['organization_name']);
        $this->assertStringContainsString('/edit', $props['editUrl']);
    }

    public function test_the_home_sections_builder_renders_its_repeating_blocks(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/home-sections');

        $response->assertOk();
        $response->assertSee('Admin\\/HomeSections', false);

        $props = $this->pageProps($response->getContent());

        $this->assertCount(4, $props['whatWeDoItems']);
        $this->assertCount(4, $props['careerIcons']);
        $this->assertSame('home_what_we_do_item_1_title', $props['whatWeDoItems'][0]['title']);
        $this->assertSame('home_career_success_hub_icon_4_image', $props['careerIcons'][3]['image']);

        // Text settings and image settings are handed over separately.
        $this->assertArrayHasKey('home_resource_block_1', $props['values']);
        $this->assertArrayNotHasKey('home_about_image', $props['values']);
        $this->assertArrayHasKey('home_about_image', $props['images']);
    }

    public function test_saving_home_sections_redirects_with_flash(): void
    {
        $this->touchedSettings[] = 'home_resource_block_1';

        $this->unguarded()
            ->actingAs($this->admin())
            ->post('/admin/home-sections', [
                'key' => ['home_resource_block_1' => '<p>OrgList smoke intro</p>'],
            ])
            ->assertRedirect('/admin/home-sections');

        $this->assertSame('Home page sections updated successfully.', session('success'));
        $this->assertSame(
            '<p>OrgList smoke intro</p>',
            SiteSettings::where('settings_name', 'home_resource_block_1')->value('settings_value'),
        );
    }

    public function test_these_screens_are_admin_only(): void
    {
        foreach (['/admin/organizations', '/admin/home-sections'] as $url) {
            $this->get($url)->assertRedirect();
        }
    }

    private function pageProps(string $html): array
    {
        $this->assertMatchesRegularExpression('/data-page="([^"]*)"/', $html);

        preg_match('/data-page="([^"]*)"/', $html, $matches);

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true)['props'];
    }
}
