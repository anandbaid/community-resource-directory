<?php

namespace Tests\Feature;

use App\Models\Banners;
use App\Models\Categories;
use App\Models\EmailTemplates;
use App\Models\Publications;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Walks every converted admin CRUD screen: each list/form renders its Vue page,
 * and each write endpoint answers with a redirect + flash (or session errors)
 * rather than the old JSON envelope.
 */
class AdminCrudInertiaTest extends TestCase
{
    private const PREFIX = 'Admin Crud Smoke ';

    protected function tearDown(): void
    {
        Categories::where('name', 'like', self::PREFIX . '%')->delete();
        EmailTemplates::where('name', 'like', self::PREFIX . '%')->delete();
        Banners::where('page_title', 'like', self::PREFIX . '%')->delete();

        Publications::where('title', 'like', self::PREFIX . '%')
            ->get()
            ->each(function (Publications $publication) {
                $publication->organizations()->detach();
                $publication->delete();
            });

        User::where('email', 'like', 'admin-crud-smoke-%@example.com')->delete();

        parent::tearDown();
    }

    /**
     * User create/reset mails through template id 2; without it the controller
     * catches the failure and flashes an error instead of redirecting.
     */
    private function seedCredentialsTemplate(): void
    {
        if (EmailTemplates::find(2)) {
            return;
        }

        $template = new EmailTemplates();
        $template->id = 2;
        $template->name = self::PREFIX . 'credentials';
        $template->title = 'Your account';
        $template->content = '<p>#Name# / #UserName# / #Password#</p>';
        $template->data = '#Name#';
        $template->status = 'active';
        $template->save();
    }

    private function admin(): User
    {
        $admin = User::where('role', 'admin')->where('status', 'active')->first();

        if (!$admin) {
            $this->markTestSkipped('No active admin user seeded.');
        }

        return $admin;
    }

    private function asAdmin()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($this->admin());
    }

    /** Every converted GET screen renders its Vue component. */
    public function test_admin_screens_render_their_vue_pages(): void
    {
        $category = $this->category();
        $user = $this->member();

        $screens = [
            '/admin/categories' => 'Categories\\/Index',
            '/admin/categories/create' => 'Categories\\/Form',
            "/admin/categories/{$category->id}/edit" => 'Categories\\/Form',
            '/admin/email-template' => 'EmailTemplates\\/Index',
            '/admin/email-template/create' => 'EmailTemplates\\/Form',
            '/admin/queries' => 'Queries\\/Index',
            '/admin/users' => 'Users\\/Index',
            '/admin/users/create' => 'Users\\/Form',
            "/admin/users/{$user->id}" => 'Users\\/Show',
            "/admin/users/{$user->id}/edit" => 'Users\\/Form',
            '/admin/banners' => 'Banners\\/Index',
            '/admin/banners/create' => 'Banners\\/Form',
            '/admin/reviews' => 'Reviews\\/Index',
            '/admin/publications' => 'Publications\\/Index',
            '/admin/publications/create' => 'Publications\\/Form',
        ];

        foreach ($screens as $url => $component) {
            $response = $this->actingAs($this->admin())->get($url);

            $response->assertOk();
            $response->assertSee($component, false);
        }
    }

    public function test_category_create_update_and_delete(): void
    {
        $this->asAdmin()
            ->post('/admin/categories', [
                'name' => self::PREFIX . 'Housing',
                'status' => 'active',
                'category_order' => 5,
            ])
            ->assertRedirect('/admin/categories');

        $this->assertSame('Category Details added successfully', session('success'));

        $category = Categories::where('name', self::PREFIX . 'Housing')->firstOrFail();

        $this->asAdmin()
            ->put("/admin/categories/{$category->id}", [
                'name' => self::PREFIX . 'Housing Renamed',
                'status' => 'inactive',
                'category_order' => 7,
            ])
            ->assertRedirect('/admin/categories');

        $category->refresh();
        $this->assertSame(self::PREFIX . 'Housing Renamed', $category->name);
        $this->assertSame('inactive', $category->status);

        $this->asAdmin()
            ->from('/admin/categories')
            ->delete("/admin/categories/{$category->id}")
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_validation_returns_session_errors(): void
    {
        $this->asAdmin()
            ->from('/admin/categories/create')
            ->post('/admin/categories', ['name' => '', 'status' => '', 'category_order' => 'abc'])
            ->assertRedirect('/admin/categories/create')
            ->assertSessionHasErrors(['name', 'status', 'category_order']);
    }

    /** Editing a user must not silently rewrite their password. */
    public function test_updating_a_user_without_reset_keeps_their_password(): void
    {
        $user = $this->member();
        $originalHash = $user->password;

        $this->asAdmin()
            ->put("/admin/users/{$user->id}", [
                'first_name' => 'Renamed',
                'last_name' => 'Person',
                'email' => $user->email,
                'phone' => '5551112222',
                'zipcode' => '35004',
            ])
            ->assertRedirect('/admin/users');

        $user->refresh();
        $this->assertSame('Renamed Person', $user->name);
        $this->assertSame($originalHash, $user->password, 'The password hash changed without a reset.');
        $this->assertTrue(Hash::check('Original-Passw0rd!', $user->password));
    }

    public function test_updating_a_user_with_reset_changes_the_password(): void
    {
        $this->seedCredentialsTemplate();
        $user = $this->member();

        $this->asAdmin()
            ->put("/admin/users/{$user->id}", [
                'first_name' => 'Reset',
                'last_name' => 'Person',
                'email' => $user->email,
                'phone' => '5551112222',
                'zipcode' => '35004',
                'reset_password' => 1,
                'password' => 'Brand-New-Passw0rd!',
                'password_confirmation' => 'Brand-New-Passw0rd!',
            ])
            ->assertRedirect('/admin/users');

        $user->refresh();
        $this->assertTrue(Hash::check('Brand-New-Passw0rd!', $user->password));
    }

    public function test_user_status_toggle_and_delete(): void
    {
        $user = $this->member();

        $this->asAdmin()
            ->from('/admin/users')
            ->patch("/admin/users/statusupdate/{$user->id}", ['status' => 'inactive'])
            ->assertRedirect('/admin/users');

        $this->assertSame('inactive', $user->refresh()->status);

        $this->asAdmin()
            ->from('/admin/users')
            ->patch("/admin/users/statusupdate/{$user->id}", ['status' => 'bogus'])
            ->assertSessionHasErrors('status');

        $this->asAdmin()
            ->from('/admin/users')
            ->delete("/admin/users/{$user->id}")
            ->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_email_template_create_and_status_toggle(): void
    {
        $this->asAdmin()
            ->post('/admin/email-template', [
                'template_name' => self::PREFIX . 'welcome',
                'template_title' => 'Welcome aboard',
                'ckeditor_data' => '<p>Hello #Name#</p>',
                'template_data' => '#Name#',
            ])
            ->assertRedirect('/admin/email-template');

        $template = EmailTemplates::where('name', self::PREFIX . 'welcome')->firstOrFail();
        $this->assertSame('<p>Hello #Name#</p>', $template->content);

        $this->asAdmin()
            ->from('/admin/email-template')
            ->patch("/admin/email-template/statusupdate/{$template->id}", ['status' => 'inactive'])
            ->assertRedirect('/admin/email-template');

        $this->assertSame('inactive', $template->refresh()->status);
    }

    public function test_banner_rejects_a_page_title_outside_the_known_list(): void
    {
        $this->asAdmin()
            ->from('/admin/banners/create')
            ->post('/admin/banners', ['page_title' => self::PREFIX . 'Nope', 'status' => 'active'])
            ->assertRedirect('/admin/banners/create')
            ->assertSessionHasErrors(['page_title', 'banner_image']);
    }

    public function test_publication_requires_a_cover_and_file_on_create(): void
    {
        $this->asAdmin()
            ->from('/admin/publications/create')
            ->post('/admin/publications', [
                'title' => self::PREFIX . 'Guide',
                'description' => 'A guide',
                'state' => 'national',
            ])
            ->assertRedirect('/admin/publications/create')
            ->assertSessionHasErrors(['cover_image', 'publication_file']);
    }

    private function category(): Categories
    {
        $category = new Categories();
        $category->name = self::PREFIX . 'Fixture';
        $category->status = 'active';
        $category->category_order = 99;
        $category->save();

        return $category;
    }

    private function member(): User
    {
        $user = new User();
        $user->first_name = 'Crud';
        $user->last_name = 'Smoke';
        $user->name = 'Crud Smoke';
        $user->email = 'admin-crud-smoke-a@example.com';
        $user->role = 'user';
        $user->status = 'active';
        $user->password = Hash::make('Original-Passw0rd!');
        $user->phone = '5551112222';
        $user->zipcode = '35004';
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }
}
