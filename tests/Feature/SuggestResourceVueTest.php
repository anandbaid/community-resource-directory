<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\OrganizationDetails;
use App\Models\Organizations;
use App\Models\PointOfContacts;
use App\Models\Publications;
use App\Models\States;
use App\Models\SuggestOrganizations;
use App\Models\User;
use Tests\TestCase;

/**
 * The suggest-a-resource form. It used to render two Blade partials over AJAX;
 * the Vue form now pulls the same values from /get-suggested-fields as JSON.
 */
class SuggestResourceVueTest extends TestCase
{
    private const PREFIX = 'Suggest Smoke ';

    protected function tearDown(): void
    {
        $userIds = User::where('email', 'like', 'suggest-smoke-%@example.com')->pluck('id');

        SuggestOrganizations::whereIn('user_id', $userIds)->delete();

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
                PointOfContacts::where('organization_id', $organization->id)->delete();
                $organization->publications()->detach();
                $organization->delete();
            });

        User::whereIn('id', $userIds)->delete();

        parent::tearDown();
    }

    private function member(): User
    {
        $user = new User();
        $user->first_name = 'Suggest';
        $user->last_name = 'Smoke';
        $user->name = 'Suggest Smoke';
        $user->email = 'suggest-smoke-a@example.com';
        $user->role = 'user';
        $user->status = 'active';
        $user->password = bcrypt('Original-Passw0rd!');
        $user->email_verified_at = now();
        $user->save();

        return $user;
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
        $organization = new Organizations();
        $organization->name = self::PREFIX . 'Org';
        $organization->type = 'government';
        $organization->phone = '5551112222';
        $organization->email = 'suggest-smoke-org@example.com';
        $organization->website = 'https://example.com';
        $organization->category = json_encode([Categories::orderBy('id')->first()?->id]);
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
        $details->service_description = 'Suggest smoke description';
        $details->social_links = json_encode(['facebook' => 'https://facebook.com/smoke']);
        $details->save();

        $contact = new PointOfContacts();
        $contact->organization_id = $organization->id;
        $contact->pronouns = 'They/Them';
        $contact->first_name = 'Sam';
        $contact->last_name = 'Doe';
        $contact->name = 'Sam Doe';
        $contact->email = 'sam@example.com';
        $contact->phone = '5559876543';
        $contact->save();

        return $organization;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'suggestion_type' => 'new',
            'organization_name' => self::PREFIX . 'Suggested',
            'organization_type' => 'government',
            'email' => 'suggested@example.com',
            'phone' => '5551112222',
            'website' => 'https://example.com',
            'service_categories' => [Categories::orderBy('id')->first()?->id],
            'service_area' => 'national',
            'target_population' => 'Adult',
            'service_description' => 'A suggestion',
            'address_1' => '1 Main St',
            'city' => 'Testville',
            'state' => $this->state()->name,
            'postcode' => '35004',
            'latitude' => '33.0',
            'longitude' => '-86.0',
        ], $overrides);
    }

    /**
     * suggestOrganizationSubmit() pings the organization's SMTP + website unless
     * the app environment is `local`; phpunit.xml pins it to `testing`. Flipping
     * it back also re-enables CSRF, which is dropped here alongside reCAPTCHA.
     */
    private function unguarded()
    {
        $this->app['env'] = 'local';

        return $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\RecaptchaProtection::class,
        ]);
    }

    public function test_both_suggest_screens_render_the_vue_page(): void
    {
        $user = $this->member();

        foreach (['/suggest-new-resources', '/suggest-existing-resources'] as $url) {
            $response = $this->actingAs($user)->get($url);

            $response->assertOk();
            $response->assertSee('Account\\/Suggest', false);
        }
    }

    public function test_get_fields_returns_blank_values_as_json(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)->getJson('/get-suggested-fields');

        $response->assertOk();
        $response->assertJsonPath('values.organization_name', '');
        $response->assertJsonPath('values.publications', []);
    }

    public function test_get_fields_prefills_from_an_existing_organization(): void
    {
        $user = $this->member();
        $organization = $this->organization();

        $response = $this->actingAs($user)
            ->getJson('/get-suggested-fields?organization=' . $organization->id);

        $response->assertOk();
        $response->assertJsonPath('values.organization_name', self::PREFIX . 'Org');
        // Bare digits, as the Vue phone input expects.
        $response->assertJsonPath('values.phone', '5551112222');
        $response->assertJsonPath('values.point_of_contact_first_name', 'Sam');
        $response->assertJsonPath('values.facebook', 'https://facebook.com/smoke');
        $response->assertJsonPath('values.service_state', $this->state()->name);
    }

    public function test_get_fields_404s_for_an_unknown_organization(): void
    {
        $this->actingAs($this->member())
            ->getJson('/get-suggested-fields?organization=99999999')
            ->assertNotFound();
    }

    public function test_submitting_a_suggestion_stores_it_and_redirects(): void
    {
        $user = $this->member();

        $this->unguarded()
            ->actingAs($user)
            ->from('/suggest-new-resources')
            ->post('/suggest-resources', $this->payload())
            ->assertRedirect('/suggest-new-resources');

        $this->assertSame('Organization Details suggested successfully', session('success'));

        $suggestion = SuggestOrganizations::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(self::PREFIX . 'Suggested', $suggestion->name);
        $this->assertSame('pending', $suggestion->status);
        $this->assertSame('National', $suggestion->service_area);
    }

    /** The posted `redirect` field used to be followed blindly. */
    public function test_submitting_ignores_an_attacker_supplied_redirect(): void
    {
        $user = $this->member();

        $this->unguarded()
            ->actingAs($user)
            ->from('/suggest-new-resources')
            ->post('/suggest-resources', $this->payload(['redirect' => 'https://evil.example.com']))
            ->assertRedirect('/suggest-new-resources');
    }

    public function test_submitting_returns_field_errors(): void
    {
        $user = $this->member();

        $this->unguarded()
            ->actingAs($user)
            ->from('/suggest-new-resources')
            ->post('/suggest-resources', $this->payload([
                'organization_name' => '',
                'postcode' => 'nope',
                'latitude' => '',
            ]))
            ->assertRedirect('/suggest-new-resources')
            ->assertSessionHasErrors(['organization_name', 'postcode', 'latitude']);
    }

    public function test_a_duplicate_publication_title_is_blocked_unless_flagged_as_an_update(): void
    {
        $user = $this->member();

        $publication = new Publications();
        $publication->title = self::PREFIX . 'Existing Guide';
        $publication->state = 'national';
        $publication->description = 'Existing';
        $publication->status = 'active';
        $publication->save();

        $withPublication = $this->payload([
            'publication_title' => [self::PREFIX . 'Existing Guide'],
            'publication_description' => ['A description'],
            'publication_state' => ['national'],
        ]);

        $this->unguarded()
            ->actingAs($user)
            ->from('/suggest-new-resources')
            ->post('/suggest-resources', $withPublication)
            ->assertSessionHasErrors('publication_title');

        $this->assertSame(0, SuggestOrganizations::where('user_id', $user->id)->count());

        // Opting in to update the existing publication lets it through.
        $this->unguarded()
            ->actingAs($user)
            ->from('/suggest-new-resources')
            ->post('/suggest-resources', array_merge($withPublication, [
                'publication_update_existing' => [1],
            ]))
            ->assertRedirect('/suggest-new-resources');

        $this->assertSame(1, SuggestOrganizations::where('user_id', $user->id)->count());
    }

    public function test_check_publication_titles_reports_duplicates(): void
    {
        $user = $this->member();

        $publication = new Publications();
        $publication->title = self::PREFIX . 'Known Title';
        $publication->state = 'national';
        $publication->description = 'Known';
        $publication->status = 'active';
        $publication->save();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($user)
            ->postJson('/check-publication-titles', [
                'titles' => [self::PREFIX . 'Known Title', self::PREFIX . 'Unknown Title'],
            ])
            ->assertOk()
            ->assertJsonCount(1, 'duplicates')
            ->assertJsonPath('duplicates.0.title', self::PREFIX . 'Known Title');
    }
}
