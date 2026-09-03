<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\EmailTemplates;
use App\Models\OrganizationDetails;
use App\Models\Organizations;
use App\Models\PointOfContacts;
use App\Models\States;
use App\Models\SuggestOrganizations;
use App\Models\User;
use Tests\TestCase;

/**
 * The admin review screen for suggested organizations.
 *
 * It used to be a third 863-line copy of the organization form; it now renders
 * OrganizationForm.vue with a different publications editor and Accept/Reject
 * in place of Save, so these tests pin the payload it is handed.
 */
class SuggestedOrganizationReviewTest extends TestCase
{
    private const PREFIX = 'Review Smoke ';

    protected function tearDown(): void
    {
        $userIds = User::where('email', 'like', 'review-smoke-%@example.com')->pluck('id');

        SuggestOrganizations::whereIn('user_id', $userIds)->delete();
        SuggestOrganizations::where('name', 'like', self::PREFIX . '%')->delete();
        EmailTemplates::where('name', 'like', 'Review Smoke%')->delete();

        Organizations::where('name', 'like', self::PREFIX . '%')
            ->get()
            ->each(function (Organizations $organization) {
                OrganizationDetails::where('organization_id', $organization->id)->delete();
                PointOfContacts::where('organization_id', $organization->id)->delete();
                $organization->publications()->detach();
                $organization->delete();
            });

        User::whereIn('id', $userIds)->delete();

        $this->member = null;

        parent::tearDown();
    }

    private function admin(): User
    {
        $user = new User();
        $user->first_name = 'Review';
        $user->last_name = 'Admin';
        $user->name = 'Review Admin';
        $user->email = 'review-smoke-admin@example.com';
        $user->role = 'admin';
        $user->status = 'active';
        $user->password = bcrypt('Original-Passw0rd!');
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private ?User $member = null;

    private function member(): User
    {
        if ($this->member) {
            return $this->member;
        }

        $user = new User();
        $user->first_name = 'Review';
        $user->last_name = 'Member';
        $user->name = 'Review Member';
        $user->email = 'review-smoke-member@example.com';
        $user->role = 'user';
        $user->status = 'active';
        $user->password = bcrypt('Original-Passw0rd!');
        $user->email_verified_at = now();
        $user->save();

        return $this->member = $user;
    }

    private function state(): States
    {
        $state = States::orderBy('name')->first();

        if (!$state) {
            $this->markTestSkipped('States are not seeded.');
        }

        return $state;
    }

    private function suggestion(array $overrides = []): SuggestOrganizations
    {
        $suggestion = new SuggestOrganizations();
        $suggestion->user_id = $this->member()->id;
        $suggestion->suggestion_type = 'new';
        $suggestion->name = self::PREFIX . 'Suggested';
        $suggestion->type = 'government';
        $suggestion->phone = '5551112222';
        $suggestion->email = 'review-smoke-org@example.com';
        $suggestion->website = 'https://example.com';
        $suggestion->category = json_encode([Categories::orderBy('id')->first()?->id]);
        $suggestion->target_population = 'Adult';
        $suggestion->service_area_type = 'national';
        $suggestion->service_area = 'National';
        $suggestion->status = 'pending';
        $suggestion->point_of_contact = json_encode([
            'pronouns' => 'They/Them',
            'first_name' => 'Sam',
            'last_name' => 'Doe',
            'email' => 'sam@example.com',
            'phone' => '5559876543',
            'notes' => 'Call in the morning',
        ]);
        $suggestion->organization_details = json_encode([
            'additional_resource' => 'A helpful extra',
            'title' => 'A title',
            'description' => 'A description',
            'file_url' => 'https://example.com/file.pdf',
            'source' => 'Referral',
            'physical_address_1' => '1 Main St',
            'physical_city' => 'Testville',
            'physical_state' => $this->state()->name,
            'physical_postal_code' => '35004',
            'latitude' => '33.0',
            'longitude' => '-86.0',
            'service_description' => 'Review smoke description',
            'social_links' => ['facebook' => 'https://facebook.com/smoke'],
        ]);
        $suggestion->publications = json_encode([
            [
                'publication_title' => self::PREFIX . 'Guide',
                'publication_description' => 'A guide',
                'publication_state' => 'national',
                'cover_file' => 'storage/publications/guide.pdf',
                'cover_image' => '',
            ],
        ]);

        foreach ($overrides as $key => $value) {
            $suggestion->{$key} = $value;
        }

        $suggestion->save();

        return $suggestion;
    }

    /** The flat payload the review form posts back. */
    private function acceptPayload(SuggestOrganizations $suggestion, array $overrides = []): array
    {
        return array_merge([
            'form_type' => 'accepted',
            'suggestion_type' => $suggestion->suggestion_type,
            'organization_name' => $suggestion->name,
            'organization_type' => 'government',
            'phone' => '5551112222',
            'email' => 'review-smoke-org@example.com',
            'website' => 'https://example.com',
            'service_categories' => [Categories::orderBy('id')->first()?->id],
            'service_area' => 'national',
            'target_population' => 'Adult',
            'service_description' => 'Review smoke description',
            'additional_resource' => 'A helpful extra',
            'title' => 'A title',
            'description' => 'A description',
            'file_url' => 'https://example.com/file.pdf',
            'source' => 'Referral',
            'address_1' => '1 Main St',
            'city' => 'Testville',
            'state' => $this->state()->name,
            'postcode' => '35004',
            'latitude' => '33.0',
            'longitude' => '-86.0',
            'point_of_contact_pronouns' => 'They/Them',
            'point_of_contact_first_name' => 'Sam',
            'point_of_contact_last_name' => 'Doe',
            'point_of_contact_email' => 'sam@example.com',
            'point_of_contact_phone' => '5559876543',
        ], $overrides);
    }

    private function seedTemplates(): void
    {
        foreach ([3 => 'Review Smoke Accepted', 4 => 'Review Smoke Rejected'] as $id => $name) {
            if (EmailTemplates::find($id)) {
                continue;
            }

            $template = new EmailTemplates();
            $template->id = $id;
            $template->name = $name;
            $template->title = $name;
            $template->content = 'Hello #Name#';
            $template->status = 'active';
            $template->save();
        }
    }

    private function unguarded()
    {
        $this->seedTemplates();

        return $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_the_index_renders_the_vue_page(): void
    {
        $suggestion = $this->suggestion();

        $response = $this->actingAs($this->admin())->get('/admin/suggested-organizations');

        $response->assertOk();
        $response->assertSee('SuggestedOrganizations\\/Index', false);
        $response->assertSee(self::PREFIX . 'Suggested', false);
        $response->assertSee((string) $suggestion->id, false);
    }

    public function test_the_review_screen_flattens_the_suggestion_into_form_values(): void
    {
        $suggestion = $this->suggestion();

        $response = $this->actingAs($this->admin())
            ->get('/admin/suggested-organizations/' . $suggestion->id . '/edit');

        $response->assertOk();
        $response->assertSee('SuggestedOrganizations\\/Review', false);

        $props = $this->pageProps($response->getContent());

        $this->assertFalse($props['readonly']);
        $this->assertSame(self::PREFIX . 'Suggested', $props['values']['organization_name']);
        $this->assertSame('Sam', $props['values']['point_of_contact_first_name']);
        $this->assertSame('https://facebook.com/smoke', $props['values']['facebook']);
        $this->assertSame('Referral', $props['values']['source']);
        $this->assertSame('33.0', $props['values']['latitude']);

        $this->assertCount(1, $props['publicationRows']);
        $this->assertSame(self::PREFIX . 'Guide', $props['publicationRows'][0]['publication_title']);
        $this->assertSame('storage/publications/guide.pdf', $props['publicationRows'][0]['cover_file_path']);
    }

    /**
     * The Blade form hardcoded value="" on these four, so accepting a
     * suggestion silently blanked them on the organization it was updating.
     */
    public function test_the_review_screen_prefills_the_fields_the_blade_form_blanked(): void
    {
        $suggestion = $this->suggestion();

        $props = $this->pageProps(
            $this->actingAs($this->admin())
                ->get('/admin/suggested-organizations/' . $suggestion->id . '/edit')
                ->getContent(),
        );

        $this->assertSame('A helpful extra', $props['values']['additional_resource']);
        $this->assertSame('A title', $props['values']['title']);
        $this->assertSame('A description', $props['values']['description']);
        $this->assertSame('https://example.com/file.pdf', $props['values']['file_url']);
    }

    public function test_an_already_reviewed_suggestion_is_read_only(): void
    {
        $suggestion = $this->suggestion(['status' => 'accepted']);

        $props = $this->pageProps(
            $this->actingAs($this->admin())
                ->get('/admin/suggested-organizations/' . $suggestion->id . '/edit')
                ->getContent(),
        );

        $this->assertTrue($props['readonly']);
    }

    public function test_accepting_creates_the_organization_and_redirects(): void
    {
        $suggestion = $this->suggestion();

        $this->unguarded()
            ->actingAs($this->admin())
            ->post(
                '/admin/suggested-organizations/' . $suggestion->id,
                $this->acceptPayload($suggestion),
            )
            ->assertRedirect('/admin/suggested-organizations');

        $this->assertSame(
            'Organization Details suggestion accepted successfully',
            session('success'),
        );

        $suggestion->refresh();
        $this->assertSame('accepted', $suggestion->status);

        $organization = Organizations::where('name', self::PREFIX . 'Suggested')->firstOrFail();
        $this->assertSame('government', $organization->type);

        $details = OrganizationDetails::where('organization_id', $organization->id)->firstOrFail();
        $this->assertSame('A helpful extra', $details->additional_resource);
        $this->assertSame('Referral', $details->source);
    }

    public function test_accepting_returns_field_errors(): void
    {
        $suggestion = $this->suggestion();

        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/suggested-organizations/' . $suggestion->id . '/edit')
            ->post(
                '/admin/suggested-organizations/' . $suggestion->id,
                $this->acceptPayload($suggestion, [
                    'organization_name' => '',
                    'website' => 'not-a-url',
                    'latitude' => '',
                ]),
            )
            ->assertRedirect('/admin/suggested-organizations/' . $suggestion->id . '/edit')
            ->assertSessionHasErrors(['organization_name', 'website', 'latitude']);

        $suggestion->refresh();
        $this->assertSame('pending', $suggestion->status);
    }

    /**
     * Validation used to run before the accept/reject branch, so a suggestion
     * whose fields were junk — the ones most in need of rejecting — could not
     * be rejected at all.
     */
    public function test_a_junk_suggestion_can_still_be_rejected(): void
    {
        $suggestion = $this->suggestion();

        $this->unguarded()
            ->actingAs($this->admin())
            ->post('/admin/suggested-organizations/' . $suggestion->id, [
                'form_type' => 'rejected',
                // Nothing else: exactly the case the old ordering blocked.
            ])
            ->assertRedirect('/admin/suggested-organizations');

        $this->assertSame(
            'Organization Details suggestion rejected successfully',
            session('success'),
        );

        $suggestion->refresh();
        $this->assertSame('rejected', $suggestion->status);
        $this->assertSame(0, Organizations::where('name', self::PREFIX . 'Suggested')->count());
    }

    public function test_a_reviewed_suggestion_cannot_be_decided_twice(): void
    {
        $suggestion = $this->suggestion(['status' => 'rejected']);

        $this->unguarded()
            ->actingAs($this->admin())
            ->from('/admin/suggested-organizations/' . $suggestion->id . '/edit')
            ->post(
                '/admin/suggested-organizations/' . $suggestion->id,
                $this->acceptPayload($suggestion),
            )
            ->assertRedirect('/admin/suggested-organizations/' . $suggestion->id . '/edit');

        $this->assertSame('This suggestion has already been reviewed.', session('error'));

        $suggestion->refresh();
        $this->assertSame('rejected', $suggestion->status);
        $this->assertSame(0, Organizations::where('name', self::PREFIX . 'Suggested')->count());
    }

    public function test_the_review_screen_is_admin_only(): void
    {
        $suggestion = $this->suggestion();

        $this->actingAs($this->member())
            ->get('/admin/suggested-organizations/' . $suggestion->id . '/edit')
            ->assertRedirect();
    }

    /** Read the Inertia page object out of the rendered root element. */
    private function pageProps(string $html): array
    {
        $this->assertMatchesRegularExpression('/data-page="([^"]*)"/', $html);

        preg_match('/data-page="([^"]*)"/', $html, $matches);

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true)['props'];
    }
}
