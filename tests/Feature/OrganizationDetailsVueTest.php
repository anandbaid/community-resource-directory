<?php

namespace Tests\Feature;

use App\Models\OrganizationDetails;
use App\Models\Organizations;
use App\Models\Publications;
use App\Models\SpamReports;
use App\Models\States;
use App\Models\User;
use Tests\TestCase;

/**
 * The public organization page. Its copy stays server rendered — this is the
 * page Google indexes — while the map, publication grid, save toggle, share
 * sheet and spam report are Vue islands mounted into that markup.
 */
class OrganizationDetailsVueTest extends TestCase
{
    private const PREFIX = 'Details Smoke ';

    protected function tearDown(): void
    {
        $userIds = User::where('email', 'like', 'details-smoke-%@example.com')->pluck('id');

        SpamReports::whereIn('user_id', $userIds)->delete();

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
                SpamReports::where('organization_id', $organization->id)->delete();
                $organization->publications()->detach();
                $organization->delete();
            });

        User::whereIn('id', $userIds)->delete();

        parent::tearDown();
    }

    private function member(): User
    {
        $user = new User();
        $user->first_name = 'Details';
        $user->last_name = 'Smoke';
        $user->name = 'Details Smoke';
        $user->email = 'details-smoke-a@example.com';
        $user->role = 'user';
        $user->status = 'active';
        $user->password = bcrypt('Original-Passw0rd!');
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private function organization(): Organizations
    {
        $state = States::orderBy('name')->first();

        if (!$state) {
            $this->markTestSkipped('States are not seeded.');
        }

        $organization = new Organizations();
        $organization->name = self::PREFIX . 'Org';
        $organization->type = 'government';
        $organization->phone = '5551112222';
        $organization->email = 'details-smoke-org@example.com';
        $organization->website = 'https://example.com';
        $organization->category = json_encode([]);
        $organization->target_population = 'Adult';
        $organization->service_area_type = 'state';
        $organization->service_area = $state->name;
        $organization->status = 'active';
        $organization->save();

        $details = new OrganizationDetails();
        $details->organization_id = $organization->id;
        $details->physical_address_1 = '1 Main St';
        $details->physical_city = 'Testville';
        $details->physical_state = $state->name;
        $details->physical_postal_code = '35004';
        $details->latitude = '33.0';
        $details->longitude = '-86.0';
        $details->service_description = 'Details smoke description';
        $details->social_links = json_encode(['facebook' => '', 'linkedin' => '', 'instagram' => '']);
        $details->save();

        return $organization;
    }

    /** @return Publications[] */
    private function publications(Organizations $organization, int $count): array
    {
        $made = [];

        for ($i = 1; $i <= $count; $i++) {
            $publication = new Publications();
            $publication->title = self::PREFIX . 'Pub ' . $i;
            $publication->state = 'national';
            $publication->description = 'Publication ' . $i;
            $publication->file = 'storage/publications/pub-' . $i . '.pdf';
            $publication->status = 'active';
            $publication->save();

            $organization->publications()->attach($publication->id);
            $made[] = $publication;
        }

        return $made;
    }

    public function test_the_page_keeps_its_copy_in_the_html_and_mounts_the_islands(): void
    {
        $organization = $this->organization();

        $response = $this->get('/organization-details/' . $organization->id);

        $response->assertOk();

        // Indexable content is still server rendered.
        $response->assertSee(self::PREFIX . 'Org', false);
        $response->assertSee('Details smoke description', false);
        $response->assertSee('Testville', false);

        // Behaviour is delegated to islands.
        $response->assertSee('data-vue-island="publication-grid"', false);
        $response->assertSee('data-vue-island="resource-map"', false);
        $response->assertSee('data-vue-island="share-modal"', false);

        // The inline Google Maps bootstrap, the jQuery handlers and the
        // server-rendered publication cards they drove are all gone.
        $response->assertDontSee('maps.googleapis.com/maps/api/js', false);
        $response->assertDontSee("$('.saved-resource')", false);
        $response->assertDontSee('class="pub-box"', false);
    }

    public function test_signed_in_members_get_the_save_and_report_islands(): void
    {
        $organization = $this->organization();

        $this->get('/organization-details/' . $organization->id)
            ->assertDontSee('data-vue-island="save-resource-toggle"', false);

        $response = $this->actingAs($this->member())
            ->get('/organization-details/' . $organization->id);

        $response->assertSee('data-vue-island="save-resource-toggle"', false);
        $response->assertSee('data-vue-island="report-spam-modal"', false);
    }

    public function test_the_first_page_of_publications_is_handed_to_the_island_as_props(): void
    {
        $organization = $this->organization();
        $this->publications($organization, 8);

        $response = $this->get('/organization-details/' . $organization->id);

        $response->assertOk();

        $props = $this->islandProps($response->getContent(), 'publication-grid');

        $this->assertCount(6, $props['publications']);
        $this->assertSame(8, $props['total']);
        $this->assertSame(url('/get-more-publication/' . $organization->id), $props['loadMoreUrl']);

        // Newest first, and every URL already resolved server side.
        $first = $props['publications'][0];
        $this->assertSame(self::PREFIX . 'Pub 8', $first['title']);
        $this->assertSame(url('download-resource/' . $first['id']), $first['downloadUrl']);
        $this->assertSame(asset('storage/publications/pub-8.pdf'), $first['file']);
        // No image on the fixture, so the shared placeholder is filled in.
        $this->assertSame(asset('assets/img/image-here.png'), $first['image']);
        $this->assertStringContainsString('facebook.com', $first['share']['facebook']);
    }

    public function test_load_more_returns_the_same_card_shape(): void
    {
        $organization = $this->organization();
        $this->publications($organization, 8);

        $response = $this->getJson('/get-more-publication/' . $organization->id . '?items=6');

        $response->assertOk();
        $response->assertJsonCount(2, 'publications');
        $response->assertJsonPath('hideBtn', true);
        $response->assertJsonStructure([
            'publications' => [['id', 'title', 'image', 'file', 'downloadUrl', 'share' => ['url', 'facebook']]],
        ]);

        // Continues where the first page stopped rather than repeating it.
        $response->assertJsonPath('publications.0.title', self::PREFIX . 'Pub 2');
    }

    public function test_load_more_keeps_the_button_alive_while_more_remain(): void
    {
        $organization = $this->organization();
        $this->publications($organization, 20);

        $this->getJson('/get-more-publication/' . $organization->id . '?items=6')
            ->assertJsonPath('hideBtn', false)
            ->assertJsonCount(6, 'publications');
    }

    public function test_reporting_spam_stores_the_report(): void
    {
        $user = $this->member();
        $organization = $this->organization();

        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\RecaptchaProtection::class,
        ])
            ->actingAs($user)
            ->postJson('/report-spam', [
                'org_id' => $organization->id,
                'spam_reason' => 'Details smoke reason',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $report = SpamReports::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Details smoke reason', $report->spam_reason);
    }

    public function test_reporting_spam_validates_the_reason(): void
    {
        $organization = $this->organization();

        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\RecaptchaProtection::class,
        ])
            ->actingAs($this->member())
            ->postJson('/report-spam', ['org_id' => $organization->id, 'spam_reason' => ''])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    /**
     * The middleware used to answer every rejection with a redirect, which an
     * XHR caller reads as a 200 full of HTML. Islands need readable JSON.
     */
    public function test_a_missing_recaptcha_token_is_rejected_as_json_for_xhr_callers(): void
    {
        $organization = $this->organization();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($this->member())
            ->postJson('/report-spam', [
                'org_id' => $organization->id,
                'spam_reason' => 'Details smoke reason',
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors', 'Recaptcha token missing.');

        $this->assertSame(0, SpamReports::where('organization_id', $organization->id)->count());
    }

    /** Blade form posts still get the redirect-with-error-bag behaviour. */
    public function test_a_missing_recaptcha_token_still_redirects_for_form_posts(): void
    {
        $organization = $this->organization();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($this->member())
            ->from('/organization-details/' . $organization->id)
            ->post('/report-spam', ['org_id' => $organization->id, 'spam_reason' => 'Reason'])
            ->assertRedirect('/organization-details/' . $organization->id)
            ->assertSessionHasErrors('captcha');
    }

    /**
     * Islands are configured through a JSON `data-vue-props` attribute, so the
     * page's contract with Vue is assertable from the rendered HTML.
     */
    private function islandProps(string $html, string $island): array
    {
        $pattern = '/data-vue-island="' . preg_quote($island, '/') . '" data-vue-props="([^"]*)"/';

        $this->assertMatchesRegularExpression($pattern, $html, "No props found for island [{$island}].");

        preg_match($pattern, $html, $matches);

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);
    }
}
