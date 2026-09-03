<?php

namespace Tests\Feature;

use App\Models\StaticPageItems;
use App\Models\StaticPages;
use App\Models\User;
use Tests\TestCase;

/**
 * The admin static-page screens.
 *
 * Three Blade forms became one Vue page plus a dedicated career editor, and the
 * GrapesJS config the create and edit views each carried a copy of now lives in
 * resources/js/lib/pageBuilder.js.
 */
class StaticPagesAdminVueTest extends TestCase
{
    private const PREFIX = 'Pages Smoke ';

    /** @var array<int, string> */
    private array $seededLegacySlugs = [];

    protected function tearDown(): void
    {
        StaticPages::where('title', 'like', self::PREFIX . '%')
            ->get()
            ->each(function (StaticPages $page) {
                StaticPageItems::where('page_id', $page->id)->delete();
                $page->delete();
            });

        // Legacy rows this test created because the scratch database had none.
        foreach ($this->seededLegacySlugs as $slug) {
            StaticPages::where('slug', $slug)
                ->get()
                ->each(function (StaticPages $page) {
                    StaticPageItems::where('page_id', $page->id)->delete();
                    $page->delete();
                });
        }

        $this->seededLegacySlugs = [];

        User::where('email', 'like', 'pages-smoke-%@example.com')->delete();

        parent::tearDown();
    }

    private function admin(): User
    {
        $user = new User();
        $user->first_name = 'Pages';
        $user->last_name = 'Admin';
        $user->name = 'Pages Admin';
        $user->email = 'pages-smoke-admin@example.com';
        $user->role = 'admin';
        $user->status = 'active';
        $user->password = bcrypt('Original-Passw0rd!');
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private function page(array $overrides = []): StaticPages
    {
        $page = new StaticPages();
        $page->title = self::PREFIX . 'Page';
        $page->slug = 'pages-smoke-page';
        $page->status = 'active';
        $page->show_in_header = false;
        $page->show_in_footer = false;

        foreach ($overrides as $key => $value) {
            $page->{$key} = $value;
        }

        $page->save();

        return $page;
    }

    /**
     * The legacy pages ship with the app's own routes, so a database that has
     * not been seeded with them still needs one to exercise those branches.
     */
    private function legacyPage(string $slug, string $title): StaticPages
    {
        $existing = StaticPages::where('slug', $slug)->first();

        if ($existing) {
            return $existing;
        }

        $this->seededLegacySlugs[] = $slug;

        return $this->page(['slug' => $slug, 'title' => self::PREFIX . $title]);
    }

    private function unguarded()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_the_index_renders_the_vue_page(): void
    {
        $page = $this->page();

        $response = $this->actingAs($this->admin())->get('/admin/static-pages');

        $response->assertOk();
        $response->assertSee('StaticPages\\/Index', false);
        $response->assertSee(self::PREFIX . 'Page', false);

        // The delete handler was inline jQuery + a raw $.ajax DELETE.
        $response->assertDontSee('delete-static-page', false);
    }

    /** Pages the site's own routes depend on must not offer a delete. */
    public function test_legacy_pages_are_marked_undeletable(): void
    {
        $this->legacyPage('about-us', 'About');

        $rows = $this->pageProps(
            $this->actingAs($this->admin())->get('/admin/static-pages')->getContent(),
        )['staticPages'];

        $row = collect($rows)->firstWhere('slug', 'about-us');

        $this->assertNotNull($row);
        $this->assertFalse($row['deletable']);
    }

    public function test_the_create_screen_renders_the_form(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/static-pages/create');

        $response->assertOk();
        $response->assertSee('StaticPages\\/Form', false);

        $props = $this->pageProps($response->getContent());

        $this->assertSame('create', $props['mode']);
        $this->assertFalse($props['isLegacy']);
        $this->assertNotEmpty($props['menuParents']);

        // GrapesJS is configured in one JS module now, not inline per view.
        $response->assertDontSee('grapesjs.init', false);
        $response->assertDontSee('unpkg.com/grapesjs', false);
    }

    public function test_a_normal_page_edits_with_the_builder(): void
    {
        $page = $this->page();

        $props = $this->pageProps(
            $this->actingAs($this->admin())
                ->get('/admin/static-pages/' . $page->id . '/edit')
                ->getContent(),
        );

        $this->assertSame('edit', $props['mode']);
        $this->assertFalse($props['isLegacy']);
        $this->assertSame(self::PREFIX . 'Page', $props['values']['title']);
        $this->assertArrayHasKey('html', $props['pageContent']);
    }

    /**
     * Legacy pages have hand-written Blade templates keyed on their slug, so
     * they edit rich-text blocks and repeatable items instead.
     */
    public function test_a_legacy_page_edits_its_blocks_and_items(): void
    {
        $legacy = $this->legacyPage('about-us', 'About');

        $props = $this->pageProps(
            $this->actingAs($this->admin())
                ->get('/admin/static-pages/' . $legacy->id . '/edit')
                ->getContent(),
        );

        $this->assertTrue($props['isLegacy']);
        $this->assertArrayHasKey('content_1', $props['values']);
        $this->assertIsArray($props['items']);
    }

    public function test_the_career_page_gets_its_own_editor(): void
    {
        $career = $this->legacyPage('career-success-hub', 'Career');

        $response = $this->actingAs($this->admin())
            ->get('/admin/static-pages/' . $career->id . '/edit');

        $response->assertOk();
        $response->assertSee('StaticPages\\/Career', false);

        $props = $this->pageProps($response->getContent());

        $this->assertCount(4, $props['segments']);
        $this->assertSame([1, 2, 3, 4], array_column($props['segments'], 'index'));

        foreach ($props['segments'] as $segment) {
            $this->assertNotSame('', $segment['text']);
            $this->assertIsArray($segment['topics']);
        }
    }

    public function test_storing_a_page_redirects_with_flash(): void
    {
        $this->unguarded()
            ->actingAs($this->admin())
            ->post('/admin/static-pages', [
                'title' => self::PREFIX . 'Created',
                'status' => 'active',
                'gjs-html' => '<p>Hello</p>',
                'gjs-css' => 'p { color: red; }',
            ])
            ->assertRedirect('/admin/static-pages');

        $this->assertSame('Static page added successfully', session('success'));

        $page = StaticPages::where('title', self::PREFIX . 'Created')->firstOrFail();
        $this->assertSame('pages-smoke-created', $page->slug);
        $this->assertSame('active', $page->status);
    }

    public function test_storing_returns_field_errors(): void
    {
        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/static-pages/create')
            ->post('/admin/static-pages', ['title' => '', 'status' => 'nope'])
            ->assertRedirect('/admin/static-pages/create')
            ->assertSessionHasErrors(['title', 'status']);
    }

    /** The header blurb is capped because the mega menu renders it inline. */
    public function test_the_header_menu_description_is_length_checked(): void
    {
        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/static-pages/create')
            ->post('/admin/static-pages', [
                'title' => self::PREFIX . 'Long',
                'status' => 'active',
                'header_menu_description' => str_repeat('a', 200),
            ])
            ->assertSessionHasErrors('header_menu_description');
    }

    public function test_updating_a_page_redirects_with_flash(): void
    {
        $page = $this->page();

        $this->unguarded()
            ->actingAs($this->admin())
            ->put('/admin/static-pages/' . $page->id, [
                'title' => self::PREFIX . 'Renamed',
                'status' => 'inactive',
            ])
            ->assertRedirect('/admin/static-pages');

        $this->assertSame('Static page updated successfully', session('success'));

        $page->refresh();
        $this->assertSame(self::PREFIX . 'Renamed', $page->title);
        $this->assertSame('inactive', $page->status);
    }

    public function test_deleting_a_page_redirects_with_flash(): void
    {
        $page = $this->page();

        $this->unguarded()
            ->actingAs($this->admin())
            ->delete('/admin/static-pages/' . $page->id)
            ->assertRedirect('/admin/static-pages');

        $this->assertSame('Static page deleted successfully.', session('success'));
        $this->assertNull(StaticPages::find($page->id));
    }

    public function test_a_legacy_page_cannot_be_deleted(): void
    {
        $legacy = $this->legacyPage('about-us', 'About');

        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/static-pages')
            ->delete('/admin/static-pages/' . $legacy->id)
            ->assertRedirect('/admin/static-pages');

        $this->assertSame('Legacy static pages cannot be deleted.', session('error'));
        $this->assertNotNull(StaticPages::find($legacy->id));
    }

    public function test_the_screens_are_admin_only(): void
    {
        $this->get('/admin/static-pages')->assertRedirect();
    }

    private function pageProps(string $html): array
    {
        $this->assertMatchesRegularExpression('/data-page="([^"]*)"/', $html);

        preg_match('/data-page="([^"]*)"/', $html, $matches);

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true)['props'];
    }
}
