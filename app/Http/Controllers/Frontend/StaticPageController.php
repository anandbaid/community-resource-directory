<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\CommonFunction;
use App\Http\Controllers\Controller;
use App\Models\Banners;
use App\Models\Queries;
use App\Models\SiteSettings;
use App\Models\StaticPageItems;
use App\Models\StaticPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaticPageController extends Controller
{
    public function aboutUs()
    {
        $staticPage = $this->pageOrPlaceholder('about-us', 'About Us');
        $staticPageItems = StaticPageItems::where('page_id', $staticPage->id)->orderBy('order', 'asc')->get();
        $banners = Banners::where('page_slug', 'abous_us')->where('status', 'active')->orderBy('order', 'asc')->get();

        return view('frontend.staticpage.about', compact('staticPage', 'staticPageItems', 'banners'));
    }
    public function career()
    {
        $staticPage = $this->pageOrPlaceholder('career-success-hub', 'Career Success Hub');
        $staticPageItems = StaticPageItems::where('page_id', $staticPage->id)->orderBy('order', 'asc')->get();
        $banners = Banners::where('page_slug', 'career-success-hub')->where('status', 'active')->orderBy('order', 'asc')->get();

        return view('frontend.staticpage.career', compact('staticPage', 'staticPageItems', 'banners'));
    }

    public function careerTopic(Request $request, StaticPageItems $topic)
    {
        $staticPage = StaticPages::where('slug', 'career-success-hub')->first();
        if (!$staticPage || (int) $topic->page_id !== (int) $staticPage->id) {
            abort(404);
        }
        $banners = Banners::where('page_slug', 'career-success-hub')
            ->where('status', 'active')
            ->orderBy('order', 'asc')
            ->get();

        return view('frontend.staticpage.career-topic', compact('staticPage', 'topic', 'banners'));
    }

    public function ourPartners()
    {
        $staticPage = $this->pageOrPlaceholder('our-partners', 'Our Partners');
        $staticPageItems = StaticPageItems::where('page_id', $staticPage->id)->orderBy('order', 'asc')->get();
        $banners = Banners::where('page_slug', 'our_partners')->where('status', 'active')->orderBy('order', 'asc')->get();
        return view('frontend.staticpage.partners', compact('staticPage', 'staticPageItems', 'banners'));
    }
    public function supportUs()
    {
        $staticPage = $this->pageOrPlaceholder('support-us', 'Support Us');
        $staticPageItems = StaticPageItems::where('page_id', $staticPage->id)->orderBy('order', 'asc')->get();
        $banners = Banners::where('page_slug', 'support_us')->where('status', 'active')->orderBy('order', 'asc')->get();
        return view('frontend.staticpage.support-us', compact('staticPage', 'staticPageItems', 'banners'));
    }

    public function contactUs()
    {
        $staticPage = $this->pageOrPlaceholder('contact-us', 'Contact Us');
        $banners = Banners::where('page_slug', 'contact')->where('status', 'active')->orderBy('order', 'asc')->get();
        return view('frontend.staticpage.contact-us', compact('staticPage', 'banners'));
    }
    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'organization' => 'required|string|max:255',
            // Was `required` alone, so anything at all reached the queries table
            // and the admin had no address to reply to.
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        try {
            $query = new Queries();
            $query->first_name = $data['first_name'];
            $query->last_name = $data['last_name'];
            $query->organization = $data['organization'];
            $query->email = $data['email'];
            $query->message = $data['message'];
            $query->save();
        } catch (\Exception $e) {
            return response()->json([
                'errors' => 'An unexpected error occurred: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }

        try {
            $this->mailContactQuery($data);
        } catch (\Exception $e) {
            // The message is already stored and visible under Queries in the
            // admin, so a mail failure must not tell the visitor to resubmit.
            report($e);
        }

        return response()->json([
            'message' => 'Contact form submitted successfully',
            'status' => 'success',
        ]);
    }

    /**
     * The admin notification interpolates the submitted values into HTML, so
     * every one of them is escaped — an unescaped `message` was markup the
     * admin's mail client would render.
     */
    private function mailContactQuery(array $data): void
    {
        $rows = [
            'First Name' => $data['first_name'],
            'Last Name' => $data['last_name'],
            'Organization' => $data['organization'],
            'Email' => $data['email'],
            'Message' => $data['message'],
        ];

        $details = '';
        foreach ($rows as $label => $value) {
            $details .= '<div><p>' . e($label) . ': ' . nl2br(e($value)) . '</p></div>';
        }

        $headerLogo = SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png';
        $copyRight = SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';
        $admin_email = SiteSettings::where('settings_name', 'admin_email')->first()->settings_value ?? '';

        CommonFunction::sendMail(
            5,
            $admin_email,
            ['#Details#', '#SiteURL#', '#SiteLogo#', '#FooterCopyright#'],
            [$details, url('/'), url($headerLogo), $copyRight],
        );
    }

    public function privacyPolicy()
    {
        $staticPage = StaticPages::where('slug', 'privacy-policy')->first();
        $staticPage = $staticPage ?? (object) [
            'title' => 'Privacy Policy',
            'description' => '',
            'content_1' => '',
            'content_2' => '',
            'content_3' => '',
            'content_4' => '',
        ];
        $banners = Banners::where('page_slug', 'privacy_policy')->where('status', 'active')->orderBy('order', 'asc')->get();
        return view('frontend.staticpage.legal', compact('staticPage', 'banners'));
    }

    public function termsConditions()
    {
        $staticPage = StaticPages::where('slug', 'terms-conditions')->first();
        $staticPage = $staticPage ?? (object) [
            'title' => 'Terms & Conditions',
            'description' => '',
            'content_1' => '',
            'content_2' => '',
            'content_3' => '',
            'content_4' => '',
        ];
        $banners = Banners::where('page_slug', 'terms_conditions')->where('status', 'active')->orderBy('order', 'asc')->get();
        return view('frontend.staticpage.legal', compact('staticPage', 'banners'));
    }

    public function dynamicPage(string $slug)
    {
        $page = StaticPages::where('slug', $slug)->where('status', 'active')->first();
        if (!$page || empty($page->content_path)) {
            return abort(404);
        }
        if (!Storage::disk('public')->exists($page->content_path)) {
            return abort(404);
        }
        $raw = Storage::disk('public')->get($page->content_path);
        $data = json_decode($raw, true) ?: [];
        $html = $data['html'] ?? '';
        $css = $data['css'] ?? '';
        $banners = Banners::where('page_slug', $page->slug)
            ->where('status', 'active')
            ->orderBy('order', 'asc')
            ->get();
        return view('frontend.staticpage.dynamic', compact('page', 'html', 'css', 'banners'));
    }

    /**
     * A static page row, or a blank stand-in carrying the same shape.
     *
     * These pages are content managed, so a row that has been renamed or not
     * created yet used to take the whole public page down with
     * "Attempt to read property on null". privacyPolicy() already guarded this
     * way; the rest did not.
     */
    private function pageOrPlaceholder(string $slug, string $title): object
    {
        return StaticPages::where('slug', $slug)->first() ?? (object) [
            'id' => null,
            'title' => $title,
            'description' => '',
            'content_1' => '',
            'content_2' => '',
            'content_3' => '',
            'content_4' => '',
        ];
    }

}
