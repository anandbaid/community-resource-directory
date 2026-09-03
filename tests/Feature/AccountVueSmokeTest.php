<?php

namespace Tests\Feature;

use App\Models\OrganizationDetails;
use App\Models\OrganizationRatings;
use App\Models\Organizations;
use App\Models\SavedResources;
use App\Models\States;
use App\Models\User;
use Tests\TestCase;

/**
 * The authenticated account area: dashboard, profile, saved resources and the
 * review form are Inertia pages, and their writes redirect with flash rather
 * than returning the old JSON envelope.
 */
class AccountVueSmokeTest extends TestCase
{
    private const PREFIX = 'Account Smoke ';

    protected function tearDown(): void
    {
        $userIds = User::where('email', 'like', 'account-smoke-%@example.com')->pluck('id');

        OrganizationRatings::whereIn('user_id', $userIds)->delete();
        SavedResources::whereIn('user_id', $userIds)->delete();

        Organizations::where('name', 'like', self::PREFIX . '%')
            ->get()
            ->each(function (Organizations $organization) {
                OrganizationDetails::where('organization_id', $organization->id)->delete();
                OrganizationRatings::where('organization_id', $organization->id)->delete();
                SavedResources::where('organization_id', $organization->id)->delete();
                $organization->delete();
            });

        User::whereIn('id', $userIds)->delete();

        parent::tearDown();
    }

    private function member(): User
    {
        $user = new User();
        $user->first_name = 'Account';
        $user->last_name = 'Smoke';
        $user->name = 'Account Smoke';
        $user->email = 'account-smoke-a@example.com';
        $user->role = 'user';
        $user->status = 'active';
        $user->password = bcrypt('Original-Passw0rd!');
        $user->phone = '5551112222';
        $user->zipcode = '35004';
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
        $organization->email = 'account-smoke-org@example.com';
        $organization->website = 'https://example.com';
        $organization->category = json_encode([]);
        $organization->target_population = 'Adult';
        $organization->service_area_type = 'national';
        $organization->service_area = 'National';
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
        $details->service_description = 'Account smoke description';
        $details->save();

        return $organization;
    }

    public function test_account_screens_render_their_vue_pages(): void
    {
        $user = $this->member();
        $organization = $this->organization();

        $screens = [
            '/dashboard' => 'Account\\/Dashboard',
            '/profile' => 'Account\\/Profile',
            '/saved-resources-view' => 'Account\\/SavedResources',
            '/saved-search-view' => 'Account\\/SavedSearches',
            '/review-rating/' . $organization->id => 'Account\\/Review',
        ];

        foreach ($screens as $url => $component) {
            $response = $this->actingAs($user)->get($url);

            $response->assertOk();
            $response->assertSee($component, false);
        }
    }

    public function test_profile_update_redirects_with_flash(): void
    {
        $user = $this->member();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($user)
            ->from('/profile')
            ->post('/profile', [
                'first_name' => 'Renamed',
                'last_name' => 'Person',
                'phone' => '5559998888',
                'zipcode' => '35005',
            ])
            ->assertRedirect('/profile');

        $this->assertSame('Profile updated successfully.', session('success'));

        $user->refresh();
        $this->assertSame('Renamed Person', $user->name);
        $this->assertSame('5559998888', $user->phone);
    }

    public function test_profile_update_returns_field_errors(): void
    {
        $user = $this->member();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($user)
            ->from('/profile')
            ->post('/profile', ['first_name' => '', 'phone' => 'abc', 'zipcode' => 'nope'])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors(['first_name', 'phone', 'zipcode']);
    }

    public function test_saved_resources_lists_the_users_saved_organizations(): void
    {
        $user = $this->member();
        $organization = $this->organization();

        $saved = new SavedResources();
        $saved->user_id = $user->id;
        $saved->organization_id = $organization->id;
        $saved->save();

        $response = $this->actingAs($user)->get('/saved-resources-view');

        $response->assertOk();
        $response->assertSee(self::PREFIX . 'Org', false);
        $response->assertSee('Account smoke description', false);
    }

    public function test_review_submit_stores_the_rating_and_hands_back_a_location(): void
    {
        $user = $this->member();
        $organization = $this->organization();

        $response = $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\RecaptchaProtection::class,
        ])
            ->actingAs($user)
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/submit-review', [
                'organization_id' => $organization->id,
                'states' => States::orderBy('name')->first()->name,
                'system_impacted' => 'no',
                'term_of_supervision' => 'no',
                'experience' => 'Personal Experience',
                'initial_interaction' => '2026-01-15',
                'structured_involvement' => 'no',
                'mandated_by_the_courts' => 'no',
                'accurate_details' => 'yes',
                'recommend' => 'yes',
                'rating' => '4',
            ]);

        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', url('/organization-details/' . $organization->id));

        $rating = OrganizationRatings::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(4, (int) $rating->rate);

        $details = json_decode($rating->description, true);
        $this->assertFalse($details['system_impacted']);
        $this->assertTrue($details['recommend']);
    }

    public function test_review_submit_returns_field_errors(): void
    {
        $user = $this->member();
        $organization = $this->organization();

        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\RecaptchaProtection::class,
        ])
            ->actingAs($user)
            ->from('/review-rating/' . $organization->id)
            ->post('/submit-review', ['organization_id' => $organization->id])
            ->assertRedirect('/review-rating/' . $organization->id)
            ->assertSessionHasErrors(['states', 'system_impacted', 'rating']);
    }

    /**
     * The conditional follow-up is only required when its trigger is chosen —
     * the Vue form mirrors this by clearing the field when the answer changes.
     */
    public function test_review_requires_legal_system_only_when_system_impacted(): void
    {
        $user = $this->member();
        $organization = $this->organization();

        $base = [
            'organization_id' => $organization->id,
            'states' => States::orderBy('name')->first()->name,
            'system_impacted' => 'yes',
            'term_of_supervision' => 'no',
            'experience' => 'Personal Experience',
            'initial_interaction' => '2026-01-15',
            'structured_involvement' => 'no',
            'mandated_by_the_courts' => 'no',
            'accurate_details' => 'yes',
            'recommend' => 'yes',
            'rating' => '4',
        ];

        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\RecaptchaProtection::class,
        ])
            ->actingAs($user)
            ->from('/review-rating/' . $organization->id)
            ->post('/submit-review', $base)
            ->assertSessionHasErrors('legal_system');
    }

    /** The reCAPTCHA middleware still guards the review endpoint. */
    public function test_review_submit_is_rejected_without_a_recaptcha_token(): void
    {
        $user = $this->member();
        $organization = $this->organization();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($user)
            ->from('/review-rating/' . $organization->id)
            ->post('/submit-review', ['organization_id' => $organization->id])
            ->assertRedirect('/review-rating/' . $organization->id)
            ->assertSessionHasErrors('captcha');
    }
}
