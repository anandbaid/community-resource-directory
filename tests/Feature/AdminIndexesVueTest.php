<?php

namespace Tests\Feature;

use App\Models\OrganizationDetails;
use App\Models\Organizations;
use App\Models\SavedSearchResources;
use App\Models\SiteSettings;
use App\Models\SpamReports;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The smaller admin screens: library and resources intros, saved searches,
 * spam reports and the bulk importer.
 *
 * The saved-search list was a server-side DataTable whose endpoint returned
 * *rendered Blade partials* as HTML strings inside its JSON; it is a paginated
 * Inertia page now, so the shape of that payload is what these pin.
 */
class AdminIndexesVueTest extends TestCase
{
    private const PREFIX = 'Indexes Smoke ';

    /** @var array<int, string> */
    private array $touchedSettings = [];

    private ?User $admin = null;

    protected function tearDown(): void
    {
        $userIds = User::where('email', 'like', 'indexes-smoke-%@example.com')->pluck('id');

        SavedSearchResources::whereIn('user_id', $userIds)->delete();
        SpamReports::whereIn('user_id', $userIds)->delete();

        Organizations::where('name', 'like', self::PREFIX . '%')
            ->get()
            ->each(function (Organizations $organization) {
                OrganizationDetails::where('organization_id', $organization->id)->delete();
                SpamReports::where('organization_id', $organization->id)->delete();
                $organization->delete();
            });

        foreach ($this->touchedSettings as $name) {
            SiteSettings::where('settings_name', $name)->delete();
        }

        User::whereIn('id', $userIds)->delete();

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
        $user->first_name = 'Indexes';
        $user->last_name = 'Admin';
        $user->name = 'Indexes Admin';
        $user->email = 'indexes-smoke-admin@example.com';
        $user->role = 'admin';
        $user->status = 'active';
        $user->password = bcrypt('Original-Passw0rd!');
        $user->email_verified_at = now();
        $user->save();

        return $this->admin = $user;
    }

    private function member(): User
    {
        $user = new User();
        $user->first_name = 'Indexes';
        $user->last_name = 'Member';
        $user->name = 'Indexes Member';
        $user->email = 'indexes-smoke-member@example.com';
        $user->role = 'user';
        $user->status = 'active';
        $user->password = bcrypt('Original-Passw0rd!');
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private function organization(): Organizations
    {
        $organization = new Organizations();
        $organization->name = self::PREFIX . 'Org';
        $organization->type = 'government';
        $organization->phone = '5551112222';
        $organization->email = 'indexes-smoke-org@example.com';
        $organization->website = 'https://example.com';
        $organization->category = json_encode([]);
        $organization->target_population = 'Adult';
        $organization->service_area_type = 'national';
        $organization->service_area = 'National';
        $organization->status = 'active';
        $organization->save();

        return $organization;
    }

    private function remember(string ...$names): void
    {
        foreach ($names as $name) {
            if (!SiteSettings::where('settings_name', $name)->exists()) {
                $this->touchedSettings[] = $name;
            }
        }
    }

    private function unguarded()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    /** Two Blade views identical apart from a setting name became one page. */
    public function test_the_library_and_resources_screens_share_one_editor(): void
    {
        $screens = [
            '/admin/library' => ['library_block', 'Library Sections'],
            '/admin/resources' => ['resource_block', 'Resources Sections'],
        ];

        foreach ($screens as $url => [$field, $title]) {
            $response = $this->actingAs($this->admin())->get($url);

            $response->assertOk();
            $response->assertSee('Admin\\/IntroEditor', false);

            $props = $this->pageProps($response->getContent());

            $this->assertSame($field, $props['field']);
            $this->assertSame($title, $props['title']);
        }
    }

    public function test_saving_the_library_block_redirects_with_flash(): void
    {
        $this->remember('library_block');

        $this->unguarded()
            ->actingAs($this->admin())
            ->post('/admin/library', ['library_block' => '<p>Indexes smoke copy</p>'])
            ->assertRedirect('/admin/library');

        $this->assertSame('Library content updated successfully.', session('success'));
        $this->assertSame(
            '<p>Indexes smoke copy</p>',
            SiteSettings::where('settings_name', 'library_block')->value('settings_value'),
        );
    }

    public function test_saving_the_resources_block_redirects_with_flash(): void
    {
        $this->remember('resource_block');

        $this->unguarded()
            ->actingAs($this->admin())
            ->post('/admin/resources', ['resource_block' => '<p>Indexes smoke resources</p>'])
            ->assertRedirect('/admin/resources');

        $this->assertSame('Resources updated successfully.', session('success'));
    }

    private function savedSearch(User $user): SavedSearchResources
    {
        $search = new SavedSearchResources();
        $search->user_id = $user->id;
        $search->search_params = json_encode(['state' => 'Alabama', 'organization_name' => self::PREFIX . 'Needle']);
        $search->save();

        return $search;
    }

    public function test_saved_searches_render_as_data_not_html(): void
    {
        $member = $this->member();
        $search = $this->savedSearch($member);

        $response = $this->actingAs($this->admin())->get('/admin/saved-searches');

        $response->assertOk();
        $response->assertSee('SavedSearches\\/Index', false);

        $props = $this->pageProps($response->getContent());
        $row = collect($props['savedSearches']['data'])->firstWhere('id', $search->id);

        $this->assertNotNull($row);
        $this->assertSame('Indexes Member', $row['user']['name']);
        $this->assertSame($member->email, $row['user']['email']);
        $this->assertStringContainsString('State: Alabama', $row['criteria']);

        // No PDF on disk, so no download is offered.
        $this->assertNull($row['downloadUrl']);

        // The old endpoint shipped rendered Blade partials in its JSON.
        $this->assertArrayNotHasKey('actions', $row);
    }

    public function test_saved_searches_can_be_searched_on_the_server(): void
    {
        $member = $this->member();
        $this->savedSearch($member);

        $props = $this->pageProps(
            $this->actingAs($this->admin())
                ->get('/admin/saved-searches?search=' . urlencode(self::PREFIX . 'Needle'))
                ->getContent(),
        );

        $this->assertSame(self::PREFIX . 'Needle', $props['filters']['search']);
        $this->assertGreaterThan(0, count($props['savedSearches']['data']));

        $missing = $this->pageProps(
            $this->actingAs($this->admin())
                ->get('/admin/saved-searches?search=no-such-search-term-here')
                ->getContent(),
        );

        $this->assertCount(0, $missing['savedSearches']['data']);
    }

    public function test_a_download_is_offered_once_the_pdf_exists(): void
    {
        $member = $this->member();
        $search = $this->savedSearch($member);

        $path = 'search-resources/' . $search->id . '-' . $member->id . '-saved-search.pdf';
        Storage::disk('public')->put($path, '%PDF-1.4 fake');

        try {
            $props = $this->pageProps(
                $this->actingAs($this->admin())->get('/admin/saved-searches')->getContent(),
            );

            $row = collect($props['savedSearches']['data'])->firstWhere('id', $search->id);
            $this->assertNotNull($row['downloadUrl']);
        } finally {
            Storage::disk('public')->delete($path);
        }
    }

    public function test_deleting_a_saved_search_removes_it_and_its_pdf(): void
    {
        $member = $this->member();
        $search = $this->savedSearch($member);

        $path = 'search-resources/' . $search->id . '-' . $member->id . '-saved-search.pdf';
        Storage::disk('public')->put($path, '%PDF-1.4 fake');

        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/saved-searches')
            ->delete('/admin/saved-searches/' . $search->id)
            ->assertRedirect('/admin/saved-searches');

        $this->assertSame('Saved search deleted successfully.', session('success'));
        $this->assertNull(SavedSearchResources::find($search->id));
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_spam_reports_render_for_every_organization(): void
    {
        $member = $this->member();
        $organization = $this->organization();

        $report = new SpamReports();
        $report->user_id = $member->id;
        $report->organization_id = $organization->id;
        $report->spam_reason = 'Indexes smoke reason';
        $report->save();

        $response = $this->actingAs($this->admin())->get('/admin/spam-report');

        $response->assertOk();
        $response->assertSee('Organizations\\/SpamReport', false);

        $props = $this->pageProps($response->getContent());

        $this->assertNull($props['organization']);

        $row = collect($props['reports'])->firstWhere('id', $report->id);
        $this->assertSame('Indexes smoke reason', $row['reason']);
        $this->assertSame('Indexes Member', $row['user']['name']);
    }

    /**
     * The id used to be read off the last URL segment rather than the route
     * parameter, so the unfiltered page matched on the string "spam-report".
     */
    public function test_spam_reports_filter_to_one_organization(): void
    {
        $member = $this->member();
        $organization = $this->organization();

        $report = new SpamReports();
        $report->user_id = $member->id;
        $report->organization_id = $organization->id;
        $report->spam_reason = 'Indexes smoke reason';
        $report->save();

        $props = $this->pageProps(
            $this->actingAs($this->admin())
                ->get('/admin/spam-report/' . $organization->id)
                ->getContent(),
        );

        $this->assertSame($organization->id, $props['organization']['id']);
        $this->assertCount(1, $props['reports']);
        $this->assertSame($report->id, $props['reports'][0]['id']);
    }

    public function test_the_bulk_import_screen_renders(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/bulk-import');

        $response->assertOk();
        $response->assertSee('Organizations\\/BulkImport', false);

        // No import has run in this database, and reading the missing report
        // file used to emit a warning rather than returning nothing.
        $this->assertNull($this->pageProps($response->getContent())['lastImport']);
    }

    /** The rule was `nullable`, and the next line dereferenced the upload. */
    public function test_the_bulk_import_requires_a_file(): void
    {
        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/bulk-import')
            ->post('/admin/bulk-import', [])
            ->assertRedirect('/admin/bulk-import')
            ->assertSessionHasErrors('uploaded_file');
    }

    public function test_these_screens_are_admin_only(): void
    {
        foreach (['/admin/saved-searches', '/admin/spam-report', '/admin/bulk-import', '/admin/library'] as $url) {
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
